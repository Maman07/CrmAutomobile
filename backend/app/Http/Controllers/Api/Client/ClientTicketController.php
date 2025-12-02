<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\BaseController;
use App\Models\TicketIntervention;
use App\Models\Vehicule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClientTicketController extends BaseController
{
    /**
     * Liste des tickets du client
     */
    public function index(Request $request): JsonResponse
    {
        $client = auth('api')->user()->client;

        if (!$client) {
            return $this->sendError('Profil client non trouvé', [], 404);
        }

        $query = TicketIntervention::where('client_id', $client->id);

        // Filtrer par statut
        if ($request->has('statut')) {
            $query->statut($request->statut);
        }

        // Filtrer par véhicule
        if ($request->has('vehicule_id')) {
            $query->where('vehicule_id', $request->vehicule_id);
        }

        $tickets = $query->with(['vehicule', 'technicien.user', 'services'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->sendPaginated($tickets, 'Liste des tickets');
    }

    /**
     * Créer un nouveau ticket
     */
    public function store(Request $request): JsonResponse
    {
        $client = auth('api')->user()->client;

        if (!$client) {
            return $this->sendError('Profil client non trouvé', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'vehicule_id' => 'required|exists:vehicules,id',
            'description' => 'required|string|min:10',
            'services' => 'required|array|min:1',
            'services.*' => 'exists:services,id',
            'date_rdv' => 'nullable|date|after:now',
            'priorite' => 'nullable|in:basse,normale,haute,urgente',
            'kilometrage_entree' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Vérifier que le véhicule appartient au client
        $vehicule = Vehicule::where('client_id', $client->id)
            ->find($request->vehicule_id);

        if (!$vehicule) {
            return $this->sendError('Véhicule non trouvé ou n\'appartient pas à ce client');
        }

        DB::beginTransaction();
        try {
            // Générer numéro ticket unique
            $annee = date('Y');
            $dernier = TicketIntervention::whereYear('created_at', $annee)->count() + 1;
            $numeroTicket = sprintf('TKT-%s-%03d', $annee, $dernier);

            // Créer le ticket
            $ticket = TicketIntervention::create([
                'client_id' => $client->id,
                'vehicule_id' => $request->vehicule_id,
                'numero_ticket' => $numeroTicket,
                'description' => $request->description,
                'statut' => 'en_attente',
                'priorite' => $request->priorite ?? 'normale',
                'kilometrage_entree' => $request->kilometrage_entree,
                'date_rdv' => $request->date_rdv,
            ]);

            // Attacher les services
            $ticket->services()->attach($request->services);

            // Mettre à jour le kilométrage du véhicule
            if ($request->kilometrage_entree) {
                $vehicule->updateKilometrage($request->kilometrage_entree);
            }

            DB::commit();

            // Charger les relations
            $ticket->load(['vehicule', 'services']);

            // TODO: Créer notification pour agents

            return $this->sendResponse($ticket, 'Ticket créé avec succès. Un agent vous contactera bientôt.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la création du ticket : ' . $e->getMessage());
        }
    }

    /**
     * Détail d'un ticket
     */
    public function show(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $ticket = TicketIntervention::where('client_id', $client->id)
            ->with(['vehicule', 'technicien.user', 'services', 'devis.lignes', 'facture'])
            ->find($id);

        if (!$ticket) {
            return $this->sendNotFound('Ticket non trouvé');
        }

        return $this->sendResponse($ticket, 'Détail du ticket');
    }

    /**
     * Modifier un ticket (uniquement si en_attente)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $ticket = TicketIntervention::where('client_id', $client->id)
            ->find($id);

        if (!$ticket) {
            return $this->sendNotFound('Ticket non trouvé');
        }

        // Autoriser modification uniquement si en attente
        if ($ticket->statut !== 'en_attente') {
            return $this->sendError('Impossible de modifier un ticket déjà pris en charge');
        }

        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|string|min:10',
            'services' => 'sometimes|array|min:1',
            'services.*' => 'exists:services,id',
            'date_rdv' => 'nullable|date|after:now',
            'priorite' => 'nullable|in:basse,normale,haute,urgente',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $ticket->update($request->only(['description', 'date_rdv', 'priorite']));

        if ($request->has('services')) {
            $ticket->services()->sync($request->services);
        }

        $ticket->load(['vehicule', 'services']);

        return $this->sendResponse($ticket, 'Ticket modifié avec succès');
    }

    /**
     * Annuler un ticket (soft)
     */
    public function destroy(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $ticket = TicketIntervention::where('client_id', $client->id)
            ->find($id);

        if (!$ticket) {
            return $this->sendNotFound('Ticket non trouvé');
        }

        // Autoriser annulation uniquement si en attente ou en diagnostic
        if (!in_array($ticket->statut, ['en_attente', 'en_diagnostic'])) {
            return $this->sendError('Impossible d\'annuler un ticket en cours de réparation');
        }

        $ticket->update(['statut' => 'annule']);

        return $this->sendSuccess('Ticket annulé avec succès');
    }

    /**
     * Suivi détaillé du ticket (timeline)
     */
    public function suivi(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $ticket = TicketIntervention::where('client_id', $client->id)
            ->with(['vehicule', 'technicien.user', 'services', 'devis', 'facture'])
            ->find($id);

        if (!$ticket) {
            return $this->sendNotFound('Ticket non trouvé');
        }

        // Timeline des événements
        $timeline = [
            [
                'etape' => 'Création',
                'statut' => 'complete',
                'date' => $ticket->created_at,
                'description' => 'Ticket créé',
            ],
        ];

        if ($ticket->date_affectation) {
            $timeline[] = [
                'etape' => 'Affectation',
                'statut' => 'complete',
                'date' => $ticket->date_affectation,
                'description' => 'Assigné au technicien ' . ($ticket->technicien->user->nom_complet ?? ''),
            ];
        }

        if ($ticket->devis) {
            $timeline[] = [
                'etape' => 'Devis',
                'statut' => $ticket->devis->statut === 'approuve' ? 'complete' : 'en_attente',
                'date' => $ticket->devis->created_at,
                'description' => 'Devis créé - Montant: ' . number_format($ticket->devis->montant_ttc, 0, ',', ' ') . ' FCFA',
            ];
        }

        if ($ticket->statut === 'en_reparation') {
            $timeline[] = [
                'etape' => 'Réparation',
                'statut' => 'en_cours',
                'date' => now(),
                'description' => 'Réparation en cours',
            ];
        }

        if ($ticket->facture && $ticket->facture->isPayee()) {
            $timeline[] = [
                'etape' => 'Paiement',
                'statut' => 'complete',
                'date' => $ticket->facture->date_paiement,
                'description' => 'Facture payée',
            ];
        }

        if ($ticket->date_cloture) {
            $timeline[] = [
                'etape' => 'Clôture',
                'statut' => 'complete',
                'date' => $ticket->date_cloture,
                'description' => 'Intervention terminée',
            ];
        }

        return $this->sendResponse([
            'ticket' => $ticket,
            'timeline' => $timeline,
        ], 'Suivi du ticket');
    }
}

