<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'type_paiement_id',
        'montant',
        'date_paiement',
        'statut',
        'reference_externe',
        'valide_par',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_paiement' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Relation avec Facture
     */
    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    /**
     * Relation avec TypePaiement
     */
    public function typePaiement()
    {
        return $this->belongsTo(TypePaiement::class);
    }

    /**
     * Agent qui a validé le paiement (pour espèces)
     */
    public function validateur()
    {
        return $this->belongsTo(Agent::class, 'valide_par');
    }

    /**
     * Boot method pour marquer facture comme payée
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($paiement) {
            if ($paiement->statut === 'confirme') {
                $facture = $paiement->facture;
                
                // Si le montant payé couvre la facture
                if ($facture->montant_paye >= $facture->montant_ttc) {
                    $facture->marquerPayee();
                }
            }
        });
    }

    /**
     * Confirmer le paiement
     */
    public function confirmer(): void
    {
        $this->update(['statut' => 'confirme']);
    }

    /**
     * Marquer comme échoué
     */
    public function marquerEchoue(): void
    {
        $this->update(['statut' => 'echoue']);
    }

    /**
     * Vérifier si le paiement est confirmé
     */
    public function isConfirme(): bool
    {
        return $this->statut === 'confirme';
    }

    /**
     * Scope pour paiements confirmés
     */
    public function scopeConfirmes($query)
    {
        return $query->where('statut', 'confirme');
    }

    /**
     * Scope pour paiements en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }
}

