<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Api\BaseController;
use App\Models\TicketIntervention;
use App\Models\User;
use App\Models\Technicien;
use Illuminate\Http\JsonResponse;

class AgentDashboardController extends BaseController
{
    /**
     * Dashboard de l'agent connecté
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        $agent = $user->agent;

        if (!$agent) {
            return $this->sendError('Profil agent non trouvé', [], 404);
        }

        // Statistiques générales
        $stats = [
            // Tickets
            'tickets_en_attente' => TicketIntervention::where('statut', 'en_attente')->count(),
            'tickets_aujourdhui' => TicketIntervention::whereDate('date_rdv', now()->toDateString())->count(),
            'total_tickets_actifs' => TicketIntervention::whereIn('statut', [
                'en_attente', 'en_diagnostic', 'devis_envoye', 'devis_approuve', 'en_reparation'
            ])->count(),
            
            // Clients
            'clients_en_attente_activation' => User::where('role', 'client')
                ->where('statut', 'inactif')
                ->count(),
            
            // Techniciens disponibles
            'techniciens_disponibles' => Technicien::whereHas('user', function ($query) {
                $query->where('statut', 'actif');
            })->count(),
        ];

        // Tickets en attente d'affectation (prioritaires)
        $tickets_a_affecter = TicketIntervention::where('statut', 'en_attente')
            ->with(['client.user', 'vehicule', 'services'])
            ->orderBy('priorite', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Nouveaux clients en attente d'activation
        $clients_en_attente = User::where('role', 'client')
            ->where('statut', 'inactif')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // RDV du jour (tous tickets)
        $rdv_aujourdhui = TicketIntervention::whereDate('date_rdv', now()->toDateString())
            ->with(['client.user', 'vehicule', 'technicien.user'])
            ->orderBy('date_rdv', 'asc')
            ->get();

        // Techniciens avec charge de travail
        $techniciens = Technicien::with(['user'])
            ->whereHas('user', function ($query) {
                $query->where('statut', 'actif');
            })
            ->withCount([
                'tickets as tickets_en_cours' => function ($query) {
                    $query->whereIn('statut', ['en_diagnostic', 'devis_envoye', 'devis_approuve', 'en_reparation']);
                }
            ])
            ->get()
            ->map(function ($tech) {
                return [
                    'id' => $tech->id,
                    'nom_complet' => $tech->user->nom_complet,
                    'specialite' => $tech->specialite,
                    'niveau_experience' => $tech->niveau_experience,
                    'tickets_en_cours' => $tech->tickets_en_cours,
                    'disponible' => $tech->tickets_en_cours < 5, // Max 5 tickets simultanés
                ];
            });

        return $this->sendResponse([
            'user' => $user->load('agent'),
            'stats' => $stats,
            'tickets_a_affecter' => $tickets_a_affecter,
            'clients_en_attente' => $clients_en_attente,
            'rdv_aujourdhui' => $rdv_aujourdhui,
            'techniciens' => $techniciens,
        ], 'Dashboard agent');
    }
}

