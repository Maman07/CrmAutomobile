<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\BaseController;
use App\Models\Paiement;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ClientPaiementController extends BaseController
{
    /**
     * Liste des paiements du client
     */
    public function index(): JsonResponse
    {
        $client = auth('api')->user()->client;

        if (!$client) {
            return $this->sendError('Profil client non trouvé', [], 404);
        }

        $paiements = Paiement::whereHas('facture.ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->with(['facture', 'typePaiement'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->sendPaginated($paiements, 'Liste des paiements');
    }

    /**
     * Initier un paiement
     */
    public function store(Request $request): JsonResponse
    {
        $client = auth('api')->user()->client;

        if (!$client) {
            return $this->sendError('Profil client non trouvé', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'facture_id' => 'required|exists:factures,id',
            'type_paiement_id' => 'required|exists:types_paiement,id',
            'montant' => 'required|numeric|min:1',
            'reference_externe' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Vérifier que la facture appartient au client
        $facture = Facture::whereHas('ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->find($request->facture_id);

        if (!$facture) {
            return $this->sendError('Facture non trouvée ou n\'appartient pas à ce client');
        }

        if ($facture->isPayee()) {
            return $this->sendError('Cette facture est déjà payée');
        }

        // RÈGLE MÉTIER : Paiement INTÉGRAL uniquement (pas de partiel)
        if ($request->montant != $facture->montant_ttc) {
            return $this->sendError(
                'Le paiement doit être intégral. Montant requis : ' . 
                number_format($facture->montant_ttc, 0, ',', ' ') . ' FCFA'
            );
        }

        // Vérifier qu'il n'y a pas déjà un paiement en attente
        $paiementEnAttente = Paiement::where('facture_id', $facture->id)
            ->where('statut', 'en_attente')
            ->exists();

        if ($paiementEnAttente) {
            return $this->sendError('Un paiement est déjà en attente de validation pour cette facture');
        }

        $paiement = Paiement::create([
            'facture_id' => $request->facture_id,
            'type_paiement_id' => $request->type_paiement_id,
            'montant' => $request->montant,
            'date_paiement' => now(),
            'statut' => 'en_attente', // Agent va valider pour espèces, API pour mobile
            'reference_externe' => $request->reference_externe,
        ]);

        // TODO: Si paiement mobile (Wave, Orange Money), appeler API
        // TODO: Si espèces, créer notification pour agent

        return $this->sendResponse($paiement, 'Paiement enregistré. En attente de validation.', 201);
    }

    /**
     * Confirmer un paiement mobile (callback API)
     */
    public function confirmer(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $paiement = Paiement::whereHas('facture.ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->find($id);

        if (!$paiement) {
            return $this->sendNotFound('Paiement non trouvé');
        }

        if ($paiement->statut === 'confirme') {
            return $this->sendError('Ce paiement est déjà confirmé');
        }

        // TODO: Vérifier auprès de l'API de paiement mobile

        $paiement->confirmer();

        return $this->sendSuccess('Paiement confirmé avec succès');
    }
}

