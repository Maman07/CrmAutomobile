<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Forcer les réponses JSON et ajouter headers API
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Forcer Accept: application/json
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Ajouter headers API
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}

