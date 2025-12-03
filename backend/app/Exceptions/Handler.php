<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Gestion des exceptions pour l'API
        $this->renderable(function (Throwable $e, $request) {
            // Uniquement pour les routes API
            if ($request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Gérer les exceptions API avec réponses JSON cohérentes
     */
    protected function handleApiException(Throwable $e, $request)
    {
        // 1. Erreur de validation (422)
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        }

        // 2. Model not found (404)
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Ressource non trouvée',
            ], 404);
        }

        // 3. Non authentifié (401)
        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié. Token manquant ou invalide.',
            ], 401);
        }

        // 4. Non autorisé (403)
        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée',
            ], 403);
        }

        // 5. Token JWT expiré
        if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json([
                'success' => false,
                'message' => 'Token expiré. Veuillez vous reconnecter.',
            ], 401);
        }

        // 6. Token JWT invalide
        if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide',
            ], 401);
        }

        // 7. Token JWT absent
        if ($e instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent. Veuillez vous connecter.',
            ], 401);
        }

        // 8. Erreur de contrainte DB (clé étrangère, unique, etc.)
        if ($e instanceof \Illuminate\Database\QueryException) {
            // Violation contrainte unique
            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette valeur existe déjà dans la base de données',
                ], 409);
            }

            // Autres erreurs DB
            return response()->json([
                'success' => false,
                'message' => 'Erreur de base de données',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne',
            ], 500);
        }

        // 9. Route non trouvée (404)
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Route API non trouvée',
            ], 404);
        }

        // 10. Méthode HTTP non autorisée (405)
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Méthode HTTP non autorisée pour cette route',
            ], 405);
        }

        // 11. Erreur générique (500)
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur interne',
            'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue',
            'trace' => config('app.debug') ? $e->getTraceAsString() : null,
        ], 500);
    }
}

