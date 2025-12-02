<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'immatriculation',
        'marque',
        'modele',
        'annee',
        'couleur',
        'numero_serie',
        'type_carburant',
        'capacite_reservoir',
        'consommation_moyenne',
        'dernier_kilometrage',
        'date_kilometrage',
        'date_ajout',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
            'capacite_reservoir' => 'integer',
            'consommation_moyenne' => 'decimal:2',
            'dernier_kilometrage' => 'integer',
            'date_kilometrage' => 'date',
            'date_ajout' => 'date',
        ];
    }

    /**
     * Relation avec Client (N-1)
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Tickets pour ce véhicule
     */
    public function tickets()
    {
        return $this->hasMany(TicketIntervention::class);
    }

    /**
     * Accesseur pour le libellé complet
     */
    public function getLibelleCompletAttribute(): string
    {
        return "{$this->marque} {$this->modele} {$this->annee}";
    }

    /**
     * Mettre à jour le kilométrage
     */
    public function updateKilometrage(int $kilometrage): void
    {
        $this->update([
            'dernier_kilometrage' => $kilometrage,
            'date_kilometrage' => now(),
        ]);
    }

    /**
     * Scope pour recherche par immatriculation
     */
    public function scopeImmatriculation($query, string $immat)
    {
        return $query->where('immatriculation', 'LIKE', "%{$immat}%");
    }
}
