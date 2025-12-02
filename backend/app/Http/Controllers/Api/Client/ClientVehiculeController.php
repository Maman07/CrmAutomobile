<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\BaseController;
use App\Models\Vehicule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ClientVehiculeController extends BaseController
{
    /**
     * Liste des véhicules du client
     */
    public function index(): JsonResponse
    {
        $client = auth('api')->user()->client;

        if (!$client) {
            return $this->sendError('Profil client non trouvé', [], 404);
        }

        $vehicules = Vehicule::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($vehicules, 'Liste des véhicules');
    }

    /**
     * Créer un nouveau véhicule
     */
    public function store(Request $request): JsonResponse
    {
        $client = auth('api')->user()->client;

        if (!$client) {
            return $this->sendError('Profil client non trouvé', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'immatriculation' => 'required|string|max:20|unique:vehicules,immatriculation',
            'marque' => 'required|string|max:100',
            'modele' => 'required|string|max:100',
            'annee' => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'couleur' => 'nullable|string|max:50',
            'numero_serie' => 'nullable|string|max:50|unique:vehicules,numero_serie',
            'type_carburant' => 'required|in:essence,diesel,hybride,electrique',
            'capacite_reservoir' => 'nullable|integer|min:1',
            'consommation_moyenne' => 'nullable|numeric|min:0',
            'dernier_kilometrage' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $vehicule = Vehicule::create(array_merge(
            $request->all(),
            ['client_id' => $client->id]
        ));

        return $this->sendResponse($vehicule, 'Véhicule créé avec succès', 201);
    }

    /**
     * Détail d'un véhicule
     */
    public function show(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $vehicule = Vehicule::where('client_id', $client->id)
            ->find($id);

        if (!$vehicule) {
            return $this->sendNotFound('Véhicule non trouvé');
        }

        // Charger l'historique des tickets
        $vehicule->load(['tickets' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return $this->sendResponse($vehicule, 'Détail du véhicule');
    }

    /**
     * Modifier un véhicule
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $vehicule = Vehicule::where('client_id', $client->id)
            ->find($id);

        if (!$vehicule) {
            return $this->sendNotFound('Véhicule non trouvé');
        }

        $validator = Validator::make($request->all(), [
            'immatriculation' => 'sometimes|string|max:20|unique:vehicules,immatriculation,' . $id,
            'marque' => 'sometimes|string|max:100',
            'modele' => 'sometimes|string|max:100',
            'annee' => 'sometimes|integer|min:1990|max:' . (date('Y') + 1),
            'couleur' => 'nullable|string|max:50',
            'type_carburant' => 'sometimes|in:essence,diesel,hybride,electrique',
            'dernier_kilometrage' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $vehicule->update($request->all());

        return $this->sendResponse($vehicule, 'Véhicule modifié avec succès');
    }

    /**
     * Supprimer un véhicule (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $vehicule = Vehicule::where('client_id', $client->id)
            ->find($id);

        if (!$vehicule) {
            return $this->sendNotFound('Véhicule non trouvé');
        }

        // Vérifier s'il n'y a pas de tickets en cours
        $ticketsEnCours = $vehicule->tickets()
            ->whereIn('statut', ['en_attente', 'en_diagnostic', 'devis_envoye', 'en_reparation'])
            ->count();

        if ($ticketsEnCours > 0) {
            return $this->sendError('Impossible de supprimer : des interventions sont en cours sur ce véhicule');
        }

        $vehicule->delete();

        return $this->sendSuccess('Véhicule supprimé avec succès');
    }

    /**
     * Historique des interventions d'un véhicule
     */
    public function historique(int $id): JsonResponse
    {
        $client = auth('api')->user()->client;

        $vehicule = Vehicule::where('client_id', $client->id)
            ->find($id);

        if (!$vehicule) {
            return $this->sendNotFound('Véhicule non trouvé');
        }

        $tickets = $vehicule->tickets()
            ->with(['technicien.user', 'services', 'devis', 'facture'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse([
            'vehicule' => $vehicule,
            'historique' => $tickets,
        ], 'Historique des interventions');
    }
}

