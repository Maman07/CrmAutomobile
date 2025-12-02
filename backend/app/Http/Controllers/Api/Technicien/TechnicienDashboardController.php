<?php

namespace App\Http\Controllers\Api\Technicien;

use App\Http\Controllers\Api\BaseController;
use App\Models\TicketIntervention;
use App\Models\Devis;
use Illuminate\Http\JsonResponse;

class TechnicienDashboardController extends BaseController
{
    /**
     * Dashboard du technicien connecté
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        $technicien = $user->technicien;

        if (!$technicien) {
            return $this->sendError('Profil technicien non trouvé', [], 404);
        }

        // Statistiques personnelles
        $stats = [
            // Tickets assignés
            'total_tickets_assignes' => TicketIntervention::where('technicien_id', $technicien->id)->count(),
            
            // Tickets en cours (non cloturés)
            'tickets_en_cours' => TicketIntervention::where('technicien_id', $technicien->id)
                ->whereIn('statut', ['en_diagnostic', 'devis_envoye', 'devis_approuve', 'en_reparation'])
                ->count(),
            
            // En attente diagnostic
            'en_diagnostic' => TicketIntervention::where('technicien_id', $technicien->id)
                ->where('statut', 'en_diagnostic')
                ->count(),
            
            // En attente approbation devis
            'devis_en_attente' => Devis::where('technicien_id', $technicien->id)
                ->where('statut', 'en_attente')
                ->count(),
            
            // En réparation (débloqués par paiement)
            'en_reparation' => TicketIntervention::where('technicien_id', $technicien->id)
                ->where('statut', 'en_reparation')
                ->count(),
            
            // Terminés ce mois
            'termines_ce_mois' => TicketIntervention::where('technicien_id', $technicien->id)
                ->whereIn('statut', ['repare', 'livre', 'cloture'])
                ->whereMonth('date_cloture', now()->month)
                ->whereYear('date_cloture', now()->year)
                ->count(),
        ];

        // Tickets prioritaires (urgents + haute priorité)
        $tickets_prioritaires = TicketIntervention::where('technicien_id', $technicien->id)
            ->whereIn('priorite', ['urgente', 'haute'])
            ->whereIn('statut', ['en_diagnostic', 'devis_approuve', 'en_reparation'])
            ->with(['vehicule.client.user', 'services'])
            ->orderBy('priorite', 'desc')
            ->orderBy('date_rdv', 'asc')
            ->limit(5)
            ->get();

        // Tickets en attente de diagnostic
        $a_diagnostiquer = TicketIntervention::where('technicien_id', $technicien->id)
            ->where('statut', 'en_diagnostic')
            ->with(['vehicule.client.user', 'services'])
            ->orderBy('date_rdv', 'asc')
            ->limit(5)
            ->get();

        // Tickets bloqués (devis approuvé mais pas payé)
        $bloques_paiement = TicketIntervention::where('technicien_id', $technicien->id)
            ->where('statut', 'devis_envoye')
            ->whereHas('devis', function ($query) {
                $query->where('statut', 'approuve');
            })
            ->whereHas('facture', function ($query) {
                $query->where('statut', 'en_attente');
            })
            ->with(['vehicule.client.user', 'facture'])
            ->get();

        // Tickets débloqués (prêts pour réparation)
        $debloques = TicketIntervention::where('technicien_id', $technicien->id)
            ->where('statut', 'devis_approuve')
            ->with(['vehicule.client.user', 'facture'])
            ->orderBy('date_rdv', 'asc')
            ->limit(5)
            ->get();

        // RDV du jour
        $rdv_aujourdhui = TicketIntervention::where('technicien_id', $technicien->id)
            ->whereDate('date_rdv', now()->toDateString())
            ->whereIn('statut', ['en_diagnostic', 'devis_approuve', 'en_reparation'])
            ->with(['vehicule.client.user', 'services'])
            ->orderBy('date_rdv', 'asc')
            ->get();

        return $this->sendResponse([
            'user' => $user->load('technicien'),
            'stats' => $stats,
            'tickets_prioritaires' => $tickets_prioritaires,
            'a_diagnostiquer' => $a_diagnostiquer,
            'bloques_paiement' => $bloques_paiement,
            'debloques' => $debloques,
            'rdv_aujourdhui' => $rdv_aujourdhui,
        ], 'Dashboard technicien');
    }
}

