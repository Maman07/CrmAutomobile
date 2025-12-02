<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\BaseController;
use App\Models\Client;
use App\Models\TicketIntervention;
use App\Models\Vehicule;
use App\Models\Facture;
use Illuminate\Http\JsonResponse;

class ClientDashboardController extends BaseController
{
    /**
     * Dashboard du client connecté
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        $client = $user->client;

        if (!$client) {
            return $this->sendError('Profil client non trouvé', [], 404);
        }

        // Statistiques
        $stats = [
            'total_vehicules' => Vehicule::where('client_id', $client->id)->count(),
            'total_tickets' => TicketIntervention::where('client_id', $client->id)->count(),
            'tickets_en_cours' => TicketIntervention::where('client_id', $client->id)
                ->whereIn('statut', ['en_attente', 'en_diagnostic', 'devis_envoye', 'en_reparation'])
                ->count(),
            'tickets_clotures' => TicketIntervention::where('client_id', $client->id)
                ->whereIn('statut', ['cloture', 'livre'])
                ->count(),
            'factures_impayees' => Facture::whereHas('ticket', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })->where('statut', 'en_attente')->count(),
        ];

        // Derniers tickets (5 plus récents)
        $derniers_tickets = TicketIntervention::where('client_id', $client->id)
            ->with(['vehicule', 'technicien.user', 'services'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Véhicules
        $vehicules = Vehicule::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Notifications non lues
        $notifications_count = $user->notifications()->nonLues()->count();

        return $this->sendResponse([
            'user' => $user->load('client'),
            'stats' => $stats,
            'derniers_tickets' => $derniers_tickets,
            'vehicules' => $vehicules,
            'notifications_non_lues' => $notifications_count,
        ], 'Dashboard client');
    }
}

