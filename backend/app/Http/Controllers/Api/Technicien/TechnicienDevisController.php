<?php

namespace App\Http\Controllers\Api\Technicien;

use App\Http\Controllers\Api\BaseController;
use App\Models\Devis;
use App\Models\LigneDevis;
use App\Models\TicketIntervention;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TechnicienDevisController extends BaseController
{
    /**
     * Créer un devis pour un ticket
     */
    public function store(Request $request, int $ticketId): JsonResponse
    {
        $technicien = auth('api')->user()->technicien;

        if (!$technicien) {
            return $this->sendError('Profil technicien non trouvé', [], 404);
        }

        // Vérifier que le ticket est assigné à ce technicien
        $ticket = TicketIntervention::where('technicien_id', $technicien->id)
            ->find($ticketId);

        if (!$ticket) {
            return $this->sendNotFound('Ticket non trouvé ou non assigné à vous');
        }

        // Vérifier qu'il n'y a pas déjà un devis
        if ($ticket->devis) {
            return $this->sendError('Un devis existe déjà pour ce ticket. Utilisez la modification.');
        }

        // Vérifier que le ticket est en diagnostic
        if ($ticket->statut !== 'en_diagnostic') {
            return $this->sendError('Le ticket doit être en diagnostic pour créer un devis');
        }

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:1000',
            'lignes' => 'required|array|min:1',
            'lignes.*.designation' => 'required|string|max:255',
            'lignes.*.type' => 'required|in:main_oeuvre,piece,fourniture',
            'lignes.*.quantite' => 'required|integer|min:1',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'date_validite_jours' => 'nullable|integer|min:1|max:90',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        DB::beginTransaction();
        try {
            // Générer numéro devis unique
            $annee = date('Y');
            $dernier = Devis::whereYear('created_at', $annee)->count() + 1;
            $numeroDevis = sprintf('DVS-%s-%03d', $annee, $dernier);

            // Date de validité (par défaut 15 jours)
            $joursValidite = $request->date_validite_jours ?? 15;
            $dateValidite = now()->addDays($joursValidite);

            // Créer le devis
            $devis = Devis::create([
                'ticket_intervention_id' => $ticket->id,
                'technicien_id' => $technicien->id,
                'numero' => $numeroDevis,
                'description' => $request->description,
                'statut' => 'en_attente',
                'date_validite' => $dateValidite,
                'montant_ht' => 0, // Sera calculé après
                'montant_tva' => 0,
                'montant_ttc' => 0,
            ]);

            // Créer les lignes du devis
            foreach ($request->lignes as $index => $ligneData) {
                LigneDevis::create([
                    'devis_id' => $devis->id,
                    'designation' => $ligneData['designation'],
                    'type' => $ligneData['type'],
                    'quantite' => $ligneData['quantite'],
                    'prix_unitaire' => $ligneData['prix_unitaire'],
                    'ordre' => $index + 1,
                    // montant calculé automatiquement via boot()
                ]);
            }

            // Calculer les totaux (méthode du Model)
            $devis->calculerMontants();

            // Mettre à jour le statut du ticket
            $ticket->update(['statut' => 'devis_envoye']);

            DB::commit();

            // Charger les relations
            $devis->load(['lignes', 'ticket.vehicule']);

            // TODO: Notification au client

            return $this->sendResponse($devis, 'Devis créé avec succès', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la création du devis : ' . $e->getMessage());
        }
    }

    /**
     * Modifier un devis (uniquement si en_attente)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $technicien = auth('api')->user()->technicien;

        $devis = Devis::where('technicien_id', $technicien->id)
            ->with(['lignes', 'ticket'])
            ->find($id);

        if (!$devis) {
            return $this->sendNotFound('Devis non trouvé ou non créé par vous');
        }

        // Autoriser modification uniquement si en attente
        if ($devis->statut !== 'en_attente') {
            return $this->sendError('Impossible de modifier un devis déjà traité');
        }

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:1000',
            'lignes' => 'required|array|min:1',
            'lignes.*.designation' => 'required|string|max:255',
            'lignes.*.type' => 'required|in:main_oeuvre,piece,fourniture',
            'lignes.*.quantite' => 'required|integer|min:1',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        DB::beginTransaction();
        try {
            // Mettre à jour la description
            $devis->update(['description' => $request->description]);

            // Supprimer anciennes lignes
            $devis->lignes()->delete();

            // Recréer les lignes
            foreach ($request->lignes as $index => $ligneData) {
                LigneDevis::create([
                    'devis_id' => $devis->id,
                    'designation' => $ligneData['designation'],
                    'type' => $ligneData['type'],
                    'quantite' => $ligneData['quantite'],
                    'prix_unitaire' => $ligneData['prix_unitaire'],
                    'ordre' => $index + 1,
                ]);
            }

            // Recalculer les totaux
            $devis->calculerMontants();

            DB::commit();

            $devis->load(['lignes', 'ticket.vehicule']);

            // TODO: Notification au client

            return $this->sendResponse($devis, 'Devis modifié avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    /**
     * Détail d'un devis
     */
    public function show(int $id): JsonResponse
    {
        $technicien = auth('api')->user()->technicien;

        $devis = Devis::where('technicien_id', $technicien->id)
            ->with(['lignes', 'ticket.vehicule.client.user', 'facture'])
            ->find($id);

        if (!$devis) {
            return $this->sendNotFound('Devis non trouvé');
        }

        return $this->sendResponse($devis, 'Détail du devis');
    }
}

