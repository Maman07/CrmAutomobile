<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends BaseController
{
    /**
     * Liste de tous les services actifs
     */
    public function index(Request $request): JsonResponse
    {
        $query = Service::query()->actif();

        // Filtrer par catégorie si demandé
        if ($request->has('categorie')) {
            $query->categorie($request->categorie);
        }

        // Recherche par libellé
        if ($request->has('search')) {
            $query->where('libelle', 'LIKE', '%' . $request->search . '%');
        }

        $services = $query->orderBy('categorie')->orderBy('libelle')->get();

        return $this->sendResponse($services, 'Liste des services');
    }

    /**
     * Détail d'un service
     */
    public function show(int $id): JsonResponse
    {
        $service = Service::find($id);

        if (! $service) {
            return $this->sendNotFound('Service non trouvé');
        }

        return $this->sendResponse($service, 'Détail du service');
    }

    /**
     * Services groupés par catégorie
     */
    public function byCategory(): JsonResponse
    {
        $servicesByCategory = Service::actif()
            ->get()
            ->groupBy('categorie');

        return $this->sendResponse($servicesByCategory, 'Services par catégorie');
    }
}
