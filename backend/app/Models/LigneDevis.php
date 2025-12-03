<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneDevis extends Model
{
    use HasFactory;

    protected $table = 'lignes_devis';

    protected $fillable = [
        'devis_id',
        'designation',
        'type',
        'quantite',
        'prix_unitaire',
        'montant',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'prix_unitaire' => 'decimal:2',
            'montant' => 'decimal:2',
            'ordre' => 'integer',
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
     * Calculer le montant automatiquement
     * (appelé via observer ou mutator)
     */
    public function calculerMontant(): void
    {
        $this->montant = $this->quantite * $this->prix_unitaire;
    }

    /**
     * Boot method pour calculer automatiquement le montant
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ligne) {
            $ligne->montant = $ligne->quantite * $ligne->prix_unitaire;
        });

        // Recalculer les totaux du devis après save/delete
        static::saved(function ($ligne) {
            $ligne->devis->calculerMontants();
        });

        static::deleted(function ($ligne) {
            $ligne->devis->calculerMontants();
        });
    }

    /**
     * Vérifier si c'est de la main d'œuvre
     */
    public function isMainOeuvre(): bool
    {
        return $this->type === 'main_oeuvre';
    }

    /**
     * Scope pour main d'œuvre uniquement
     */
    public function scopeMainOeuvre($query)
    {
        return $query->where('type', 'main_oeuvre');
    }

    /**
     * Scope pour pièces uniquement
     */
    public function scopePieces($query)
    {
        return $query->whereIn('type', ['piece', 'fourniture']);
    }
}

