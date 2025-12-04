<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Api\BaseController;
use App\Models\TicketIntervention;
use App\Models\User;
use App\Models\Facture;
use App\Models\Paiement;
use App\Models\Vehicule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ManagerDashboardController extends BaseController
{
    /**
     * Dashboard du manager avec statistiques globales
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        $manager = $user->manager;

        if (!$manager) {
            return $this->sendError('Profil manager non trouvé', [], 404);
        }

        // KPIs principaux
        $stats = [
            // Clients
            'total_clients' => User::where('role', 'client')->where('statut', 'actif')->count(),
            'nouveaux_clients_mois' => User::where('role', 'client')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            
            // Personnel
            'total_techniciens' => User::where('role', 'technicien')->where('statut', 'actif')->count(),
            'total_agents' => User::where('role', 'agent')->where('statut', 'actif')->count(),
            
            // Tickets
            'total_tickets' => TicketIntervention::count(),
            'tickets_en_cours' => TicketIntervention::whereIn('statut', [
                'en_attente', 'en_diagnostic', 'devis_envoye', 'devis_approuve', 'en_reparation'
            ])->count(),
            'tickets_clotures_mois' => TicketIntervention::whereIn('statut', ['cloture', 'livre'])
                ->whereMonth('date_cloture', now()->month)
                ->whereYear('date_cloture', now()->year)
                ->count(),
            
            // Véhicules
            'total_vehicules' => Vehicule::count(),
            
            // Financier (ce mois)
            'ca_mois' => Facture::where('statut', 'payee')
                ->whereMonth('date_paiement', now()->month)
                ->whereYear('date_paiement', now()->year)
                ->sum('montant_ttc'),
            
            'factures_impayees_montant' => Facture::where('statut', 'en_attente')
                ->sum('montant_ttc'),
            
            'factures_impayees_nombre' => Facture::where('statut', 'en_attente')->count(),
        ];

        // Évolution tickets (6 derniers mois) - PostgreSQL compatible
        $evolutionTickets = TicketIntervention::select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mois"),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('mois')
            ->orderBy('mois', 'asc')
            ->get();

        // Évolution CA (6 derniers mois) - PostgreSQL compatible
        $evolutionCA = Facture::select(
                DB::raw("TO_CHAR(date_paiement, 'YYYY-MM') as mois"),
                DB::raw('SUM(montant_ttc) as total')
            )
            ->where('statut', 'payee')
            ->where('date_paiement', '>=', now()->subMonths(6))
            ->groupBy('mois')
            ->orderBy('mois', 'asc')
            ->get();

        // Tickets par statut
        $ticketsParStatut = TicketIntervention::select('statut', DB::raw('COUNT(*) as nombre'))
            ->groupBy('statut')
            ->get();

        // Top 5 clients (par nombre de tickets)
        $topClients = DB::table('tickets_intervention')
            ->join('clients', 'tickets_intervention.client_id', '=', 'clients.id')
            ->join('users', 'clients.user_id', '=', 'users.id')
            ->select(
                'users.nom',
                'users.prenom',
                'users.email',
                DB::raw('COUNT(tickets_intervention.id) as nb_tickets')
            )
            ->groupBy('clients.id', 'users.nom', 'users.prenom', 'users.email')
            ->orderBy('nb_tickets', 'desc')
            ->limit(5)
            ->get();

        // Techniciens performance (tickets cloturés ce mois)
        $performanceTechniciens = DB::table('tickets_intervention')
            ->join('techniciens', 'tickets_intervention.technicien_id', '=', 'techniciens.id')
            ->join('users', 'techniciens.user_id', '=', 'users.id')
            ->select(
                'users.nom',
                'users.prenom',
                'techniciens.specialite',
                DB::raw('COUNT(tickets_intervention.id) as tickets_clotures')
            )
            ->whereIn('tickets_intervention.statut', ['cloture', 'livre'])
            ->whereMonth('tickets_intervention.date_cloture', now()->month)
            ->whereYear('tickets_intervention.date_cloture', now()->year)
            ->groupBy('techniciens.id', 'users.nom', 'users.prenom', 'techniciens.specialite')
            ->orderBy('tickets_clotures', 'desc')
            ->get();

        // Alertes
        $alertes = [
            'factures_echues' => Facture::echues()->count(),
            'tickets_non_affectes' => TicketIntervention::where('statut', 'en_attente')
                ->whereNull('technicien_id')
                ->count(),
            'clients_en_attente' => User::where('role', 'client')
                ->where('statut', 'inactif')
                ->count(),
            'paiements_a_verifier' => Paiement::where('statut', 'en_attente')
                ->whereHas('typePaiement', function ($query) {
                    $query->whereIn('libelle', ['Virement bancaire', 'Chèque']);
                })
                ->count(),
        ];

        return $this->sendResponse([
            'user' => $user->load('manager'),
            'stats' => $stats,
            'evolution_tickets' => $evolutionTickets,
            'evolution_ca' => $evolutionCA,
            'tickets_par_statut' => $ticketsParStatut,
            'top_clients' => $topClients,
            'performance_techniciens' => $performanceTechniciens,
            'alertes' => $alertes,
        ], 'Dashboard manager');
    }
}

