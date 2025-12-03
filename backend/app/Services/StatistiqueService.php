<?php

namespace App\Services;

use App\Models\TicketIntervention;
use App\Models\Facture;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatistiqueService
{
    /**
     * Calculer le chiffre d'affaires pour une période
     */
    public function calculateCA(Carbon $dateDebut, Carbon $dateFin): float
    {
        return Facture::where('statut', 'payee')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->sum('montant_ttc');
    }

    /**
     * CA du mois en cours
     */
    public function caMoisCourant(): float
    {
        return $this->calculateCA(
            now()->startOfMonth(),
            now()->endOfMonth()
        );
    }

    /**
     * Évolution du CA sur N mois
     */
    public function evolutionCA(int $nombreMois = 6): array
    {
        $results = Facture::select(
                DB::raw("TO_CHAR(date_paiement, 'YYYY-MM') as mois"),
                DB::raw('SUM(montant_ttc) as total')
            )
            ->where('statut', 'payee')
            ->where('date_paiement', '>=', now()->subMonths($nombreMois))
            ->groupBy(DB::raw("TO_CHAR(date_paiement, 'YYYY-MM')"))
            ->orderBy('mois', 'asc')
            ->get();

        return $results->mapWithKeys(function ($item) {
            return [$item->mois => (float) $item->total];
        })->toArray();
    }

    /**
     * Taux de conversion devis → facture payée
     */
    public function tauxConversionDevis(): float
    {
        $totalDevis = \App\Models\Devis::count();
        $devisApprouves = \App\Models\Devis::where('statut', 'approuve')->count();

        if ($totalDevis === 0) {
            return 0;
        }

        return round(($devisApprouves / $totalDevis) * 100, 2);
    }

    /**
     * Délai moyen de réparation (en jours)
     */
    public function delaiMoyenReparation(): float
    {
        $tickets = TicketIntervention::whereNotNull('date_affectation')
            ->whereNotNull('date_cloture')
            ->get();

        if ($tickets->isEmpty()) {
            return 0;
        }

        $totalJours = $tickets->sum(function ($ticket) {
            return $ticket->date_affectation->diffInDays($ticket->date_cloture);
        });

        return round($totalJours / $tickets->count(), 1);
    }

    /**
     * Répartition tickets par statut
     */
    public function repartitionTicketsParStatut(): array
    {
        return TicketIntervention::select('statut', DB::raw('COUNT(*) as nombre'))
            ->groupBy('statut')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->statut => $item->nombre];
            })
            ->toArray();
    }

    /**
     * Top N clients (par nombre de tickets)
     */
    public function topClients(int $limit = 10): array
    {
        return DB::table('tickets_intervention')
            ->join('clients', 'tickets_intervention.client_id', '=', 'clients.id')
            ->join('users', 'clients.user_id', '=', 'users.id')
            ->select(
                'users.nom',
                'users.prenom',
                'users.email',
                DB::raw('COUNT(tickets_intervention.id) as nb_tickets'),
                DB::raw('SUM(CASE WHEN tickets_intervention.statut IN (\'cloture\', \'livre\') THEN 1 ELSE 0 END) as nb_termines')
            )
            ->groupBy('clients.id', 'users.nom', 'users.prenom', 'users.email')
            ->orderBy('nb_tickets', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Performance techniciens (tickets terminés sur période)
     */
    public function performanceTechniciens(Carbon $dateDebut, Carbon $dateFin): array
    {
        return DB::table('tickets_intervention')
            ->join('techniciens', 'tickets_intervention.technicien_id', '=', 'techniciens.id')
            ->join('users', 'techniciens.user_id', '=', 'users.id')
            ->select(
                'users.nom',
                'users.prenom',
                'techniciens.specialite',
                DB::raw('COUNT(tickets_intervention.id) as nb_tickets_termines'),
                DB::raw('AVG(EXTRACT(DAY FROM (tickets_intervention.date_cloture - tickets_intervention.date_affectation))) as delai_moyen_jours')
            )
            ->whereIn('tickets_intervention.statut', ['cloture', 'livre'])
            ->whereBetween('tickets_intervention.date_cloture', [$dateDebut, $dateFin])
            ->groupBy('techniciens.id', 'users.nom', 'users.prenom', 'techniciens.specialite')
            ->orderBy('nb_tickets_termines', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Taux de satisfaction (si système d'évaluation implémenté)
     * TODO: Implémenter système d'évaluation client
     */
    public function tauxSatisfaction(): float
    {
        // TODO: Calculer depuis table evaluations
        return 0;
    }
}

