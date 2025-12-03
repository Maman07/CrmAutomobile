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
            'telephone' => 'nullable|string|regex:/^\+221[0-9]{9}$/',
        ], [
            'telephone.regex' => 'Le numéro doit être au format sénégalais : +221XXXXXXXXX',
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

        // RÈGLE MÉTIER : Paiement INTÉGRAL uniquement
        if ($request->montant != $facture->montant_ttc) {
            return $this->sendError(
                'Le paiement doit être intégral. Montant requis : ' . 
                number_format($facture->montant_ttc, 0, ',', ' ') . ' FCFA'
            );
        }

        // Vérifier qu'il n'y a pas déjà un paiement en attente
        $paiementEnAttente = Paiement::where('facture_id', $facture->id)
            ->whereIn('statut', ['en_attente', 'confirme'])
            ->exists();

        if ($paiementEnAttente) {
            return $this->sendError('Un paiement existe déjà pour cette facture');
        }

        $typePaiement = \App\Models\TypePaiement::find($request->type_paiement_id);

        // Créer le paiement
        $paiement = Paiement::create([
            'facture_id' => $request->facture_id,
            'type_paiement_id' => $request->type_paiement_id,
            'montant' => $request->montant,
            'date_paiement' => now(),
            'statut' => 'en_attente',
            'reference_externe' => null,
            'metadata' => [
                'telephone' => $request->telephone,
                'type' => $typePaiement->libelle,
            ],
        ]);

        // SI MOBILE MONEY : Message pour l'utilisateur
        if (in_array($typePaiement->libelle, ['Wave', 'Orange Money', 'Free Money'])) {
            return $this->sendResponse($paiement, 
                'Paiement initié. Vous recevrez une notification pour confirmer le paiement.', 
                201
            );
        }

        // SI VIREMENT/CHÈQUE : Attente vérification comptable
        return $this->sendResponse($paiement, 
            'Paiement enregistré. Il sera vérifié sous 24-48h.', 
            201
        );
    }
}
