<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ============================================
// 1. ROUTES PUBLIQUES (Sans authentification)
// ============================================

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    
    // Login avec rate limiting strict (anti brute-force)
    Route::middleware('throttle:login')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });
    
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Services publics (consultation catalogue)
Route::get('services', [\App\Http\Controllers\Api\ServiceController::class, 'index']);
Route::get('services/by-category', [\App\Http\Controllers\Api\ServiceController::class, 'byCategory']);
Route::get('services/{id}', [\App\Http\Controllers\Api\ServiceController::class, 'show']);

// ============================================
// 2. ROUTES AUTHENTIFIÉES (JWT Required)
// ============================================

Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    
    // Auth (logout, refresh token, user info)
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Notifications (accessible à tous les rôles authentifiés)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::get('unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
        Route::post('{id}/mark-as-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('mark-all-as-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
        Route::delete('{id}', [\App\Http\Controllers\Api\NotificationController::class, 'destroy']);
    });

    // ============================================
    // 3. ROUTES CLIENT
    // ============================================
    Route::middleware('role:client')->prefix('client')->group(function () {
        // Dashboard
        Route::get('dashboard', [\App\Http\Controllers\Api\Client\ClientDashboardController::class, 'index']);
        
        // Profil
        Route::get('profile', [\App\Http\Controllers\Api\Client\ClientProfileController::class, 'show']);
        Route::put('profile', [\App\Http\Controllers\Api\Client\ClientProfileController::class, 'update']);
        
        // Véhicules
        Route::apiResource('vehicules', \App\Http\Controllers\Api\Client\ClientVehiculeController::class);
        Route::get('vehicules/{id}/historique', [\App\Http\Controllers\Api\Client\ClientVehiculeController::class, 'historique']);
        
        // Tickets
        Route::apiResource('tickets', \App\Http\Controllers\Api\Client\ClientTicketController::class);
        Route::get('tickets/{id}/suivi', [\App\Http\Controllers\Api\Client\ClientTicketController::class, 'suivi']);
        
        // Devis
        Route::get('devis', [\App\Http\Controllers\Api\Client\ClientDevisController::class, 'index']);
        Route::get('devis/{id}', [\App\Http\Controllers\Api\Client\ClientDevisController::class, 'show']);
        Route::post('devis/{id}/approuver', [\App\Http\Controllers\Api\Client\ClientDevisController::class, 'approuver']);
        Route::post('devis/{id}/refuser', [\App\Http\Controllers\Api\Client\ClientDevisController::class, 'refuser']);
        Route::get('devis/{id}/pdf', [\App\Http\Controllers\Api\Client\ClientDevisController::class, 'downloadPdf']);
        
        // Factures
        Route::get('factures', [\App\Http\Controllers\Api\Client\ClientFactureController::class, 'index']);
        Route::get('factures/{id}', [\App\Http\Controllers\Api\Client\ClientFactureController::class, 'show']);
        Route::get('factures/{id}/pdf', [\App\Http\Controllers\Api\Client\ClientFactureController::class, 'downloadPdf']);
        
        // Paiements
        Route::post('paiements', [\App\Http\Controllers\Api\Client\ClientPaiementController::class, 'store']);
        Route::get('paiements', [\App\Http\Controllers\Api\Client\ClientPaiementController::class, 'index']);
        
        // Notifications
        Route::get('notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::post('notifications/{id}/lire', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    });

    // ============================================
    // 4. ROUTES TECHNICIEN
    // ============================================
    Route::middleware('role:technicien')->prefix('technicien')->group(function () {
        // Dashboard
        Route::get('dashboard', [\App\Http\Controllers\Api\Technicien\TechnicienDashboardController::class, 'index']);
        
        // Mes tickets assignés
        Route::get('tickets', [\App\Http\Controllers\Api\Technicien\TechnicienTicketController::class, 'index']);
        Route::get('tickets/{id}', [\App\Http\Controllers\Api\Technicien\TechnicienTicketController::class, 'show']);
        Route::put('tickets/{id}/statut', [\App\Http\Controllers\Api\Technicien\TechnicienTicketController::class, 'updateStatut']);
        
        // Devis
        Route::post('tickets/{ticketId}/devis', [\App\Http\Controllers\Api\Technicien\TechnicienDevisController::class, 'store']);
        Route::put('devis/{id}', [\App\Http\Controllers\Api\Technicien\TechnicienDevisController::class, 'update']);
        Route::get('devis/{id}', [\App\Http\Controllers\Api\Technicien\TechnicienDevisController::class, 'show']);
        
        // Historique
        Route::get('historique', [\App\Http\Controllers\Api\Technicien\TechnicienHistoriqueController::class, 'index']);
        
        // Profil
        Route::get('profile', [\App\Http\Controllers\Api\Technicien\TechnicienProfileController::class, 'show']);
        Route::put('profile', [\App\Http\Controllers\Api\Technicien\TechnicienProfileController::class, 'update']);
    });

    // ============================================
    // 5. ROUTES AGENT
    // ============================================
    Route::middleware('role:agent')->prefix('agent')->group(function () {
        // Dashboard
        Route::get('dashboard', [\App\Http\Controllers\Api\Agent\AgentDashboardController::class, 'index']);
        
        // Gestion tickets
        Route::get('tickets', [\App\Http\Controllers\Api\Agent\AgentTicketController::class, 'index']);
        Route::get('tickets/{id}', [\App\Http\Controllers\Api\Agent\AgentTicketController::class, 'show']);
        Route::post('tickets/{id}/affecter', [\App\Http\Controllers\Api\Agent\AgentTicketController::class, 'affecter']);
        Route::post('tickets/{id}/reaffecter', [\App\Http\Controllers\Api\Agent\AgentTicketController::class, 'reaffecter']);
        
        // Gestion clients
        Route::get('clients/en-attente', [\App\Http\Controllers\Api\Agent\AgentClientController::class, 'enAttente']);
        Route::post('clients/{id}/activer', [\App\Http\Controllers\Api\Agent\AgentClientController::class, 'activer']);
        Route::post('clients/{id}/rejeter', [\App\Http\Controllers\Api\Agent\AgentClientController::class, 'rejeter']);
        
        // Calendrier
        Route::get('calendrier', [\App\Http\Controllers\Api\Agent\AgentCalendrierController::class, 'index']);
        
        // Profil
        Route::get('profile', [\App\Http\Controllers\Api\Agent\AgentProfileController::class, 'show']);
    });

    // ============================================
    // 6. ROUTES MANAGER
    // ============================================
    Route::middleware('role:manager')->prefix('manager')->group(function () {
        // Dashboard & Statistiques
        Route::get('dashboard', [\App\Http\Controllers\Api\Manager\ManagerDashboardController::class, 'index']);
        Route::get('statistiques', [\App\Http\Controllers\Api\Manager\ManagerStatistiqueController::class, 'index']);
        
        // Gestion personnel
        Route::apiResource('personnel', \App\Http\Controllers\Api\Manager\ManagerPersonnelController::class);
        Route::post('personnel/{id}/desactiver', [\App\Http\Controllers\Api\Manager\ManagerPersonnelController::class, 'desactiver']);
        Route::post('personnel/{id}/activer', [\App\Http\Controllers\Api\Manager\ManagerPersonnelController::class, 'activer']);
        
        // Gestion services
        Route::apiResource('services', \App\Http\Controllers\Api\Manager\ManagerServiceController::class);
        
        // Gestion paiements
        Route::get('paiements/en-attente', [\App\Http\Controllers\Api\Manager\ManagerPaiementController::class, 'enAttente']);
        Route::get('paiements/historique', [\App\Http\Controllers\Api\Manager\ManagerPaiementController::class, 'historique']);
        Route::get('paiements/{id}', [\App\Http\Controllers\Api\Manager\ManagerPaiementController::class, 'show']);
        Route::get('paiements/{id}/justificatif', [\App\Http\Controllers\Api\Manager\ManagerPaiementController::class, 'voirJustificatif']);
        Route::post('paiements/{id}/confirmer', [\App\Http\Controllers\Api\Manager\ManagerPaiementController::class, 'confirmer']);
        Route::post('paiements/{id}/rejeter', [\App\Http\Controllers\Api\Manager\ManagerPaiementController::class, 'rejeter']);
        
        // Rapports
        Route::get('rapports', [\App\Http\Controllers\Api\Manager\ManagerRapportController::class, 'index']);
        Route::post('rapports/generer', [\App\Http\Controllers\Api\Manager\ManagerRapportController::class, 'generer']);
        Route::get('rapports/{id}/download', [\App\Http\Controllers\Api\Manager\ManagerRapportController::class, 'download']);
        
        // Supervision (logs)
        Route::get('supervision/logs', [\App\Http\Controllers\Api\Manager\ManagerSupervisionController::class, 'logs']);
        
        // Paramètres système
        Route::get('parametres', [\App\Http\Controllers\Api\Manager\ManagerParametreController::class, 'index']);
        Route::put('parametres/{cle}', [\App\Http\Controllers\Api\Manager\ManagerParametreController::class, 'update']);
        
        // Calendrier global
        Route::get('calendrier', [\App\Http\Controllers\Api\Manager\ManagerCalendrierController::class, 'index']);
    });
});

