<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Api\BaseController;
use App\Models\TicketIntervention;
use App\Models\Technicien;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AgentTicketController extends BaseController
{
    /**
     * Liste de tous les tickets (vue agent)
     */
    public function index(Request $request): JsonResponse
    {
        $query = TicketIntervention::query();

        // Filtrer par statut
        if ($request->has('statut')) {
            $query->statut($request->statut);
        }

        // Filtrer par technicien
        if ($request->has('technicien_id')) {
            $query->where('technicien_id', $request->technicien_id);
        }

        // Filtrer par priorité
        if ($request->has('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        // Tickets en attente (non affectés)
        if ($request->has('non_affectes') && $request->non_affectes) {
            $query->whereNull('technicien_id');
        }

        $tickets = $query->with(['client.user', 'vehicule', 'technicien.user', 'services'])
            ->orderBy('priorite', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return $this->sendPaginated($tickets, 'Liste des tickets');
    }

    /**
     * Détail d'un ticket (vue agent)
     */
    public function show(int $id): JsonResponse
    {
        $ticket = TicketIntervention::with([
            'client.user',
            'vehicule',
            'technicien.user',
            'services',
            'devis.lignes',
            'facture.paiements'
        ])->find($id);

        if (!$ticket) {
            return $this->sendNotFound('Ticket non trouvé');
        }

        return $this->sendResponse($ticket, 'Détail du ticket');
    }

    /**
     * Affecter un ticket à un technicien
     */
    public function affecter(Request $request, int $id): JsonResponse
    {
        $ticket = TicketIntervention::find($id);

        if (!$ticket) {
            return $this->sendNotFound('Ticket non trouvé');
        }

        // Vérifier que le ticket est en attente
        if ($ticket->statut !== 'en_attente') {
            return $this->sendError('Seuls les tickets en attente peuvent être affectés');
        }

        $validator = Validator::make($request->all(), [
            'technicien_id' => 'required|exists:techniciens,id',
            'date_rdv' => 'nullable|date|after:now',
            'observation' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Vérifier que le technicien existe et est actif
        $technicien = Technicien::with('user')
            ->whereHas('user', function ($query) {
                $query->where('statut', 'actif');
            })
            ->find($request->technicien_id);

        if (!$technicien) {
            return $this->sendError('Technicien non trouvé ou inactif');
        }

        // Vérifier la charge de travail du technicien
        $ticketsEnCours = TicketIntervention::where('technicien_id', $technicien->id)
            ->whereIn('statut', ['en_diagnostic', 'devis_envoye', 'devis_approuve', 'en_reparation'])
            ->count();

        if ($ticketsEnCours >= 5) {
            return $this->sendError(
                'Ce technicien a déjà 5 tickets en cours. Choisissez un autre technicien ou attendez qu\'il termine ses interventions en cours.'
            );
        }

        // Affecter le ticket
        $ticket->affecterTechnicien($request->technicien_id);

        if ($request->has('date_rdv')) {
            $ticket->update(['date_rdv' => $request->date_rdv]);
        }

        if ($request->has('observation')) {
            $ticket->update(['observation' => $request->observation]);
        }

        $ticket->load(['technicien.user', 'client.user', 'vehicule']);

        // TODO: Notification au technicien et au client

        return $this->sendResponse($ticket, 'Ticket affecté avec succès au technicien ' . $technicien->user->nom_complet);
    }

    /**
     * Réaffecter un ticket à un autre technicien
     */
    public function reaffecter(Request $request, int $id): JsonResponse
    {
        $ticket = TicketIntervention::find($id);

        if (!$ticket) {
            return $this->sendNotFound('Ticket non trouvé');
        }

        // Vérifier que le ticket est déjà affecté
        if (!$ticket->technicien_id) {
            return $this->sendError('Ce ticket n\'est pas encore affecté');
        }

        // Interdire réaffectation si en réparation ou terminé
        if (in_array($ticket->statut, ['en_reparation', 'repare', 'livre', 'cloture'])) {
            return $this->sendError('Impossible de réaffecter un ticket en cours de réparation ou terminé');
        }

        $validator = Validator::make($request->all(), [
            'technicien_id' => 'required|exists:techniciens,id|different:' . $ticket->technicien_id,
            'motif' => 'required|string|min:10|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $ancienTechnicien = $ticket->technicien;

        // Vérifier le nouveau technicien
        $nouveauTechnicien = Technicien::with('user')
            ->whereHas('user', function ($query) {
                $query->where('statut', 'actif');
            })
            ->find($request->technicien_id);

        if (!$nouveauTechnicien) {
            return $this->sendError('Nouveau technicien non trouvé ou inactif');
        }

        // Réaffecter
        $ticket->update([
            'technicien_id' => $request->technicien_id,
            'date_affectation' => now(),
            'statut' => 'en_diagnostic',
            'observation' => 'Réaffectation : ' . $request->motif,
        ]);

        $ticket->load(['technicien.user', 'client.user']);

        // TODO: Notifications (ancien tech, nouveau tech, client)

        return $this->sendResponse($ticket, 
            'Ticket réaffecté de ' . $ancienTechnicien->user->nom_complet . 
            ' à ' . $nouveauTechnicien->user->nom_complet
        );
    }
}

