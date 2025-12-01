<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Les chemins sur lesquels CORS sera appliqué.
    | 'api/*' : Toutes les routes API
    | 'sanctum/csrf-cookie' : Pour l'authentification Sanctum (si utilisé)
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Les méthodes HTTP autorisées pour les requêtes cross-origin.
    | ['*'] autorise toutes les méthodes : GET, POST, PUT, PATCH, DELETE, OPTIONS
    |
    */

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Les origines autorisées à faire des requêtes vers l'API.
    | En développement : http://localhost:4200 (Angular)
    | En production : Remplacer par le vrai domaine frontend
    |
    | ⚠️ IMPORTANT : Ne JAMAIS mettre ['*'] en production (faille de sécurité)
    |
    */

    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:4200')],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns d'origines autorisées (regex).
    | Exemple : ['#^https://.*\.example\.com$#']
    |
    */

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Les headers autorisés dans les requêtes cross-origin.
    | ['*'] autorise tous les headers, incluant :
    | - Authorization (pour JWT)
    | - Content-Type
    | - Accept
    | - X-Requested-With
    |
    */

    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Les headers que le frontend peut lire dans la réponse.
    | 'Authorization' : Permet au frontend de récupérer le token JWT
    |
    */

    'exposed_headers' => ['Authorization'],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Durée (en secondes) pendant laquelle le navigateur peut mettre en cache
    | la réponse de la requête preflight (OPTIONS).
    | 3600 = 1 heure
    |
    */

    'max_age' => 3600,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Indique si les requêtes peuvent inclure des credentials (cookies, headers Authorization).
    | Doit être TRUE pour JWT et authentification basée sur les cookies.
    |
    */

    'supports_credentials' => true,

];

