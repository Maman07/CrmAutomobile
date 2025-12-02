<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\BaseController;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientFactureController extends BaseController
{
    /**
     * Liste des factures du client
     */
    public function index(Request $request): JsonResponse
    {
        $client = auth('api')->user()->client;

        if (!$client) {
            return $this->sendError('Profil client non trouvé', [], 404);
        }

        $query = Facture::whereHas('ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        });

        // Filtrer par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        // Factures échues
        if ($request->has('echues') && $request->echues) {
            $query->echues();
        }

        $factures = $query->with(['ticket.vehicule', 'devis', 'paiements'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->sendPaginated($factures, 'Liste des factures');
    }

    /**
     * Détail d'une facture
     */
    public function show(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $facture = Facture::whereHas('ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->with(['ticket.vehicule', 'devis.lignes', 'paiements.typePaiement'])
            ->find($id);

        if (!$facture) {
            return $this->sendNotFound('Facture non trouvée');
        }

        // Ajouter montants calculés
        $facture->montant_paye_total = $facture->montant_paye;
        $facture->reste_a_payer = $facture->reste_a_payer;
        $facture->est_echue = $facture->isEchue();

        return $this->sendResponse($facture, 'Détail de la facture');
    }

    /**
     * Télécharger le PDF de la facture
     */
    public function downloadPdf(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $facture = Facture::whereHas('ticket', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->with(['ticket.vehicule.client.user', 'devis.lignes'])
            ->find($id);

        if (!$facture) {
            return $this->sendNotFound('Facture non trouvée');
        }

        // TODO: Générer PDF

        return $this->sendResponse([
            'facture' => $facture,
            'message' => 'TODO: Génération PDF à implémenter',
        ], 'Données de la facture pour PDF');
    }
}

