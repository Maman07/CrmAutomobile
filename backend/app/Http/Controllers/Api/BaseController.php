<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Réponse de succès
     */
    protected function sendResponse($data, string $message = 'Opération réussie', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Réponse d'erreur
     */
    protected function sendError(string $message, $errors = [], int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Réponse de succès sans données
     */
    protected function sendSuccess(string $message = 'Opération réussie', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $code);
    }

    /**
     * Réponse paginée
     */
    protected function sendPaginated($paginator, string $message = 'Liste récupérée'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], 200);
    }

    /**
     * Réponse non trouvé
     */
    protected function sendNotFound(string $message = 'Ressource non trouvée'): JsonResponse
    {
        return $this->sendError($message, [], 404);
    }

    /**
     * Réponse non autorisé
     */
    protected function sendUnauthorized(string $message = 'Non autorisé'): JsonResponse
    {
        return $this->sendError($message, [], 401);
    }

    /**
     * Réponse accès interdit
     */
    protected function sendForbidden(string $message = 'Accès refusé'): JsonResponse
    {
        return $this->sendError($message, [], 403);
    }

    /**
     * Réponse erreur de validation
     */
    protected function sendValidationError($errors, string $message = 'Erreur de validation'): JsonResponse
    {
        return $this->sendError($message, $errors, 422);
    }
}

