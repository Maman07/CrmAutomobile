<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\BaseController;
use App\Models\Devis;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClientDevisController extends BaseController
{
    /**
     * Liste des devis du client
     */
    public function index(Request $request): JsonResponse
    {
        $client = auth('api')->user()->client;

        if (!$client) {
            return $this->sendError('Profil client non trouvé', [], 404);
        }

        $query = Devis::whereHas('ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        });

        // Filtrer par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        $devis = $query->with(['ticket.vehicule', 'technicien.user', 'lignes'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->sendPaginated($devis, 'Liste des devis');
    }

    /**
     * Détail d'un devis avec lignes
     */
    public function show(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $devis = Devis::whereHas('ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->with(['ticket.vehicule', 'technicien.user', 'lignes', 'facture'])
            ->find($id);

        if (!$devis) {
            return $this->sendNotFound('Devis non trouvé');
        }

        return $this->sendResponse($devis, 'Détail du devis');
    }

    /**
     * Approuver un devis
     */
    public function approuver(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $devis = Devis::whereHas('ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->find($id);

        if (!$devis) {
            return $this->sendNotFound('Devis non trouvé');
        }

        if ($devis->statut !== 'en_attente') {
            return $this->sendError('Ce devis a déjà été traité');
        }

        if ($devis->isExpire()) {
            return $this->sendError('Ce devis est expiré. Contactez le garage pour un nouveau devis.');
        }

        DB::beginTransaction();
        try {
            // Approuver le devis
            $devis->approuver();

            // Générer la facture automatiquement
            $facture = Facture::creerDepuisDevis($devis);

            DB::commit();

            // TODO: Notification au technicien et agent

            return $this->sendResponse([
                'devis' => $devis,
                'facture' => $facture,
            ], 'Devis approuvé avec succès. Facture générée. Vous avez 48h pour effectuer le paiement.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de l\'approbation : ' . $e->getMessage());
        }
    }

    /**
     * Refuser un devis
     */
    public function refuser(Request $request, int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $devis = Devis::whereHas('ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->find($id);

        if (!$devis) {
            return $this->sendNotFound('Devis non trouvé');
        }

        if ($devis->statut !== 'en_attente') {
            return $this->sendError('Ce devis a déjà été traité');
        }

        $validator = Validator::make($request->all(), [
            'motif' => 'required|string|min:10|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $devis->refuser($request->motif);

        // TODO: Notification au technicien

        return $this->sendSuccess('Devis refusé. Le garage va vous recontacter.');
    }

    /**
     * Télécharger le PDF du devis
     */
    public function downloadPdf(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $devis = Devis::whereHas('ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->with(['ticket.vehicule.client.user', 'technicien.user', 'lignes'])
            ->find($id);

        if (!$devis) {
            return $this->sendNotFound('Devis non trouvé');
        }

        // TODO: Générer PDF avec DomPDF ou similar
        // Pour l'instant, retourner les données

        return $this->sendResponse([
            'devis' => $devis,
            'message' => 'TODO: Génération PDF à implémenter',
        ], 'Données du devis pour PDF');
    }
}

