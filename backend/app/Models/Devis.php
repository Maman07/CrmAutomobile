<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Devis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_intervention_id',
        'technicien_id',
        'numero',
        'description',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'statut',
        'date_validite',
        'date_approbation',
        'motif_refus',
    ];

    protected function casts(): array
    {
        return [
            'montant_ht' => 'decimal:2',
            'montant_tva' => 'decimal:2',
            'montant_ttc' => 'decimal:2',
            'date_validite' => 'date',
            'date_approbation' => 'datetime',
        ];
    }

    /**
     * Relation avec TicketIntervention
     */
    public function ticket()
    {
        return $this->belongsTo(TicketIntervention::class, 'ticket_intervention_id');
    }

    /**
     * Relation avec Technicien
     */
    public function technicien()
    {
        return $this->belongsTo(Technicien::class);
    }

    /**
     * Lignes du devis
     */
    public function lignes()
    {
        return $this->hasMany(LigneDevis::class)->orderBy('ordre');
    }

    /**
     * Facture générée depuis ce devis
     */
    public function facture()
    {
        return $this->hasOne(Facture::class);
    }

    /**
     * Calculer les montants (appelé avant save)
     */
    public function calculerMontants(): void
    {
        $this->montant_ht = $this->lignes->sum('montant');
        $this->montant_tva = $this->montant_ht * 0.18; // 18% TVA
        $this->montant_ttc = $this->montant_ht + $this->montant_tva;
        $this->save();
    }

    /**
     * Approuver le devis
     */
    public function approuver(): void
    {
        $this->update([
            'statut' => 'approuve',
            'date_approbation' => now(),
        ]);

        // Mettre à jour le statut du ticket
        // ⚠️ IMPORTANT : Reste sur "devis_envoye" jusqu'au paiement
        // Le statut passera à "devis_approuve" automatiquement après paiement confirmé
        $this->ticket->update(['statut' => 'devis_envoye']); // Garde statut actuel
    }

    /**
     * Refuser le devis
     */
    public function refuser(string $motif): void
    {
        $this->update([
            'statut' => 'refuse',
            'motif_refus' => $motif,
        ]);
    }

    /**
     * Vérifier si le devis est expiré
     */
    public function isExpire(): bool
    {
        return $this->date_validite && $this->date_validite->isPast();
    }

    /**
     * Vérifier si le devis est approuvé
     */
    public function isApprouve(): bool
    {
        return $this->statut === 'approuve';
    }

    /**
     * Scope pour devis en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour devis approuvés
     */
    public function scopeApprouves($query)
    {
        return $query->where('statut', 'approuve');
    }
}

