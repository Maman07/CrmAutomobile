<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Api\BaseController;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ManagerPaiementController extends BaseController
{
    /**
     * Liste des paiements en attente de vérification
     */
    public function enAttente(): JsonResponse
    {
        $paiements = Paiement::where('statut', 'en_attente')
            ->with([
                'facture.ticket.client.user',
                'facture.ticket.vehicule',
                'typePaiement'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->sendPaginated($paiements, 'Paiements en attente de vérification');
    }

    /**
     * Détail d'un paiement avec toutes les infos
     */
    public function show(int $id): JsonResponse
    {
        $paiement = Paiement::with([
            'facture.ticket.client.user',
            'facture.ticket.vehicule',
            'facture.devis.lignes',
            'typePaiement'
        ])->find($id);

        if (!$paiement) {
            return $this->sendNotFound('Paiement non trouvé');
        }

        // Ajouter URL du justificatif si existe
        if ($paiement->justificatif) {
            $paiement->justificatif_url = Storage::url($paiement->justificatif);
        }

        return $this->sendResponse($paiement, 'Détail du paiement');
    }

    /**
     * Télécharger le justificatif (PDF/image)
     */
    public function voirJustificatif(int $id): JsonResponse
    {
        $paiement = Paiement::find($id);

        if (!$paiement) {
            return $this->sendNotFound('Paiement non trouvé');
        }

        if (!$paiement->justificatif) {
            return $this->sendError('Aucun justificatif n\'a été uploadé pour ce paiement');
        }

        // Vérifier que le fichier existe
        if (!Storage::disk('public')->exists($paiement->justificatif)) {
            return $this->sendError('Le fichier justificatif est introuvable sur le serveur');
        }

        // Retourner l'URL publique
        $url = Storage::url($paiement->justificatif);
        return $this->sendResponse([
            'justificatif_url' => url($url),
            'nom_fichier' => basename($paiement->justificatif),
            'type' => Storage::mimeType('public/' . $paiement->justificatif),
            'taille' => Storage::size('public/' . $paiement->justificatif),
        ], 'Justificatif disponible');
    }

    /**
     * Confirmer un paiement après vérification
     */
    public function confirmer(Request $request, int $id): JsonResponse
    {
        $paiement = Paiement::with(['facture.ticket'])->find($id);

        if (!$paiement) {
            return $this->sendNotFound('Paiement non trouvé');
        }

        if ($paiement->statut !== 'en_attente') {
            return $this->sendError('Ce paiement a déjà été traité');
        }

        $validator = Validator::make($request->all(), [
            'reference_bancaire' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Confirmer le paiement
        $paiement->update([
            'statut' => 'confirme',
            'reference_externe' => $request->reference_bancaire,
            'metadata' => array_merge(
                $paiement->metadata ?? [],
                [
                    'verifie_par' => auth('api')->user()->nom_complet,
                    'date_verification' => now()->toDateTimeString(),
                    'commentaire' => $request->commentaire,
                ]
            ),
        ]);

        // Le Model Paiement boot() va automatiquement :
        // 1. Marquer facture comme payée
        // 2. Débloquer le ticket (statut → "devis_approuve")

        return $this->sendResponse($paiement, 
            'Paiement confirmé avec succès. La facture est maintenant payée et le ticket est débloqué pour réparation.'
        );
    }

    /**
     * Rejeter un paiement
     */
    public function rejeter(Request $request, int $id): JsonResponse
    {
        $paiement = Paiement::with(['facture.ticket.client.user'])->find($id);

        if (!$paiement) {
            return $this->sendNotFound('Paiement non trouvé');
        }

        if ($paiement->statut !== 'en_attente') {
            return $this->sendError('Ce paiement a déjà été traité');
        }

        $validator = Validator::make($request->all(), [
            'motif' => 'required|string|min:10|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Rejeter le paiement
        $paiement->update([
            'statut' => 'echoue',
            'metadata' => array_merge(
                $paiement->metadata ?? [],
                [
                    'rejete_par' => auth('api')->user()->nom_complet,
                    'date_rejet' => now()->toDateTimeString(),
                    'motif_rejet' => $request->motif,
                ]
            ),
        ]);

        return $this->sendSuccess(
            'Paiement rejeté. Le client sera notifié.'
        );
    }

    /**
     * Historique de tous les paiements (confirmés + rejetés)
     */
    public function historique(Request $request): JsonResponse
    {
        $query = Paiement::with([
            'facture.ticket.client.user',
            'typePaiement'
        ]);

        // Filtrer par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtrer par type paiement
        if ($request->has('type_paiement_id')) {
            $query->where('type_paiement_id', $request->type_paiement_id);
        }

        // Filtrer par date
        if ($request->has('date_debut')) {
            $query->whereDate('date_paiement', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('date_paiement', '<=', $request->date_fin);
        }

        $paiements = $query->orderBy('date_paiement', 'desc')
            ->paginate(20);

        return $this->sendPaginated($paiements, 'Historique des paiements');
    }
}
