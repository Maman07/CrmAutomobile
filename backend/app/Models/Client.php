<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'adresse',
        'ville',
        'type_client',
        'nom_entreprise',
    ];

    /**
     * Relation avec User (1-1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec Vehicules (1-N)
     */
    public function vehicules()
    {
        return $this->hasMany(Vehicule::class);
    }

    /**
     * Relation avec Tickets (1-N)
     */
    public function tickets()
    {
        return $this->hasMany(TicketIntervention::class);
    }

    /**
     * Vérifier si c'est une entreprise
     */
    public function isEntreprise(): bool
    {
        return $this->type_client === 'entreprise';
    }

    /**
     * Scope pour les entreprises
     */
    public function scopeEntreprises($query)
    {
        return $query->where('type_client', 'entreprise');
    }

    /**
     * Scope pour les particuliers
     */
    public function scopeParticuliers($query)
    {
        return $query->where('type_client', 'particulier');
    }
}
