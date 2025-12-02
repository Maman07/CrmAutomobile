<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'categorie',
        'description',
        'prix_indicatif',
        'duree_moyenne',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'prix_indicatif' => 'decimal:2',
            'duree_moyenne' => 'integer',
            'actif' => 'boolean',
        ];
    }

    /**
     * Relation Many-to-Many avec Tickets
     */
    public function tickets()
    {
        return $this->belongsToMany(
            TicketIntervention::class,
            'ticket_service',
            'service_id',
            'ticket_intervention_id'
        )->withTimestamps();
    }

    /**
     * Scope pour services actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeCategorie($query, string $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    /**
     * Activer/désactiver le service
     */
    public function toggleActif(): void
    {
        $this->update(['actif' => !$this->actif]);
    }
}
