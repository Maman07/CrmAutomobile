<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends BaseController
{
    /**
     * Liste de tous les services actifs (route publique)
     */
    public function index(): JsonResponse
    {
        $services = Service::actif()->get();
        
        return $this->sendResponse($services, 'Liste des services');
    }

    /**
     * Détail d'un service (route publique)
     */
    public function show($id): JsonResponse
    {
        $service = Service::find($id);

        if (!$service) {
            return $this->sendNotFound('Service non trouvé');
        }

        return $this->sendResponse($service, 'Détail du service');
    }
}

