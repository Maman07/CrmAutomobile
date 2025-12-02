<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'matricule',
        'date_embauche',
        'poste',
    ];

    protected function casts(): array
    {
        return [
            'date_embauche' => 'date',
        ];
    }

    /**
     * Relation avec User (1-1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Paiements validés par cet agent
     */
    public function paiementsValides()
    {
        return $this->hasMany(Paiement::class, 'valide_par');
    }

    /**
     * Calculer l'ancienneté en années
     */
    public function getAncienneteAttribute(): int
    {
        return $this->date_embauche->diffInYears(now());
    }
}
