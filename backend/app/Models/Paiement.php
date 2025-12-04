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
        'justificatif',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_paiement' => 'datetime',
            'justificatif' => 'string',
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
     * Boot method pour marquer facture comme payée
     */
    protected static function boot()
    {
        parent::boot();

        // Vérifier avant création : pas de paiement confirmé existant
        static::creating(function ($paiement) {
            $paiementExistant = self::where('facture_id', $paiement->facture_id)
                ->where('statut', 'confirme')
                ->exists();

            if ($paiementExistant) {
                throw new \Exception('Cette facture a déjà été payée. Paiement multiple interdit.');
            }
        });

        // Après confirmation du paiement
        static::saved(function ($paiement) {
            if ($paiement->statut === 'confirme') {
                $facture = $paiement->facture;
                
                // RÈGLE MÉTIER : Le paiement doit être intégral
                if ($paiement->montant == $facture->montant_ttc) {
                    $facture->marquerPayee();
                    
                    // DÉBLOCAGE : Passer le ticket en "devis_approuve" pour permettre réparation
                    $ticket = $facture->ticket;
                    if ($ticket && $ticket->statut === 'devis_envoye') {
                        $ticket->update(['statut' => 'devis_approuve']);
                    }
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
