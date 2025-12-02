<?php

namespace App\Http\Controllers\Api\Technicien;

use App\Http\Controllers\Api\BaseController;
use App\Models\TicketIntervention;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TechnicienTicketController extends BaseController
{
    /**
     * Liste des tickets assignés au technicien connecté
     */
    public function index(Request $request): JsonResponse
    {
        $technicien = auth('api')->user()->technicien;

        if (!$technicien) {
            return $this->sendError('Profil technicien non trouvé', [], 404);
        }

        $query = TicketIntervention::where('technicien_id', $technicien->id);

        // Filtrer par statut
        if ($request->has('statut')) {
            $query->statut($request->statut);
        }

        $tickets = $query->with(['vehicule.client.user', 'services', 'devis', 'facture'])
            ->orderBy('priorite', 'desc')
            ->orderBy('date_rdv', 'asc')
            ->paginate(10);

        return $this->sendPaginated($tickets, 'Mes tickets assignés');
    }

    /**
     * Détail d'un ticket
     */
    public function show(int $id): JsonResponse
    {
        $technicien = auth('api')->user()->technicien;

        $ticket = TicketIntervention::where('technicien_id', $technicien->id)
            ->with(['vehicule.client.user', 'services', 'devis.lignes', 'facture.paiements'])
            ->find($id);

        if (!$ticket) {
            return $this->sendNotFound('Ticket non trouvé ou non assigné à vous');
        }

        return $this->sendResponse($ticket, 'Détail du ticket');
    }

    /**
     * Changer le statut du ticket
     * RÈGLE MÉTIER : Paiement obligatoire avant passage à "en_reparation"
     */
    public function updateStatut(Request $request, int $id): JsonResponse
    {
        $technicien = auth('api')->user()->technicien;

        $ticket = TicketIntervention::where('technicien_id', $technicien->id)
            ->with(['devis', 'facture'])
            ->find($id);

        if (!$ticket) {
            return $this->sendNotFound('Ticket non trouvé ou non assigné à vous');
        }

        $validator = Validator::make($request->all(), [
            'statut' => 'required|in:en_diagnostic,devis_envoye,en_reparation,repare,livre',
            'observation' => 'nullable|string',
            'kilometrage_sortie' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $nouveauStatut = $request->statut;

        // RÈGLE MÉTIER CRITIQUE : Vérifier paiement avant "en_reparation"
        if ($nouveauStatut === 'en_reparation') {
            // Vérifier qu'il y a un devis approuvé
            if (!$ticket->devis || $ticket->devis->statut !== 'approuve') {
                return $this->sendError('Le devis doit être approuvé par le client avant de commencer la réparation');
            }

            // Vérifier qu'il y a une facture
            if (!$ticket->facture) {
                return $this->sendError('Aucune facture générée. Impossible de commencer la réparation.');
            }

            // 🔒 BLOCAGE PRINCIPAL : Vérifier que la facture est PAYÉE
            if (!$ticket->facture->isPayee()) {
                return $this->sendError(
                    '⚠️ PAIEMENT REQUIS : Le client doit d\'abord payer la facture (' . 
                    number_format($ticket->facture->montant_ttc, 0, ',', ' ') . 
                    ' FCFA) avant que vous puissiez commencer la réparation.'
                );
            }
        }

        // Validation logique des transitions de statut
        $transitionsValides = [
            'en_diagnostic' => ['devis_envoye'],
            'devis_envoye' => ['en_diagnostic'], // Retour si modification devis
            'devis_approuve' => ['en_reparation'], // Seulement si payé (vérifié ci-dessus)
            'en_reparation' => ['repare'],
            'repare' => ['livre'],
        ];

        $statutActuel = $ticket->statut;

        if (isset($transitionsValides[$statutActuel]) && 
            !in_array($nouveauStatut, $transitionsValides[$statutActuel])) {
            return $this->sendError("Transition de statut invalide : {$statutActuel} → {$nouveauStatut}");
        }

        // Mettre à jour le ticket
        $ticket->update([
            'statut' => $nouveauStatut,
            'observation' => $request->observation,
            'kilometrage_sortie' => $request->kilometrage_sortie,
        ]);

        // Si livré, marquer la date de clôture
        if ($nouveauStatut === 'livre') {
            $ticket->update(['date_cloture' => now()]);
        }

        // TODO: Créer notification pour le client

        return $this->sendResponse($ticket, 'Statut du ticket mis à jour');
    }
}

