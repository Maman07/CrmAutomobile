<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facture extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'devis_id',
        'ticket_intervention_id',
        'numero',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'statut',
        'date_emission',
        'date_echeance',
        'date_paiement',
    ];

    protected function casts(): array
    {
        return [
            'montant_ht' => 'decimal:2',
            'montant_tva' => 'decimal:2',
            'montant_ttc' => 'decimal:2',
            'date_emission' => 'date',
            'date_echeance' => 'date',
            'date_paiement' => 'datetime',
        ];
    }

    /**
     * Relation avec Devis
     */
    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

    /**
     * Relation avec TicketIntervention
     */
    public function ticket()
    {
        return $this->belongsTo(TicketIntervention::class, 'ticket_intervention_id');
    }

    /**
     * Paiements associés à cette facture
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    /**
     * Créer une facture depuis un devis approuvé
     */
    public static function creerDepuisDevis(Devis $devis): self
    {
        return self::create([
            'devis_id' => $devis->id,
            'ticket_intervention_id' => $devis->ticket_intervention_id,
            'numero' => self::genererNumero(),
            'montant_ht' => $devis->montant_ht,
            'montant_tva' => $devis->montant_tva,
            'montant_ttc' => $devis->montant_ttc,
            'statut' => 'en_attente',
            'date_emission' => now(),
            'date_echeance' => now()->addHours(48), // 48h pour payer
        ]);
    }

    /**
     * Générer un numéro de facture unique
     */
    public static function genererNumero(): string
    {
        $annee = date('Y');
        $dernier = self::whereYear('created_at', $annee)->count() + 1;
        return sprintf('FAC-%s-%03d', $annee, $dernier);
    }

    /**
     * Marquer comme payée
     */
    public function marquerPayee(): void
    {
        $this->update([
            'statut' => 'payee',
            'date_paiement' => now(),
        ]);
    }

    /**
     * Vérifier si la facture est payée
     */
    public function isPayee(): bool
    {
        return $this->statut === 'payee';
    }

    /**
     * Vérifier si la facture est échue
     */
    public function isEchue(): bool
    {
        return $this->date_echeance && $this->date_echeance->isPast() && ! $this->isPayee();
    }

    /**
     * Calculer le montant total payé
     */
    public function getMontantPayeAttribute(): float
    {
        return $this->paiements()
            ->where('statut', 'confirme')
            ->sum('montant');
    }

    /**
     * Calculer le reste à payer
     */
    public function getResteAPayerAttribute(): float
    {
        return $this->montant_ttc - $this->montant_paye;
    }

    /**
     * Scope pour factures impayées
     */
    public function scopeImpayees($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour factures échues
     */
    public function scopeEchues($query)
    {
        return $query->where('statut', 'en_attente')
                     ->where('date_echeance', '<', now());
    }
}

