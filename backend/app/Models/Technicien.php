<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technicien extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'matricule',
        'date_embauche',
        'specialite',
        'niveau_experience',
        'certifications',
    ];

    protected function casts(): array
    {
        return [
            'date_embauche' => 'date',
            'certifications' => 'array',
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
     * Tickets assignés à ce technicien
     */
    public function tickets()
    {
        return $this->hasMany(TicketIntervention::class);
    }

    /**
     * Devis créés par ce technicien
     */
    public function devis()
    {
        return $this->hasMany(Devis::class);
    }

    /**
     * Vérifier si technicien est expert
     */
    public function isExpert(): bool
    {
        return $this->niveau_experience === 'expert';
    }

    /**
     * Scope pour filtrer par spécialité
     */
    public function scopeSpecialite($query, string $specialite)
    {
        return $query->where('specialite', $specialite);
    }

    /**
     * Scope pour techniciens experts
     */
    public function scopeExperts($query)
    {
        return $query->where('niveau_experience', 'expert');
    }

    /**
     * Calculer le nombre de tickets en cours
     */
    public function getTicketsEnCoursCountAttribute(): int
    {
        return $this->tickets()
            ->whereIn('statut', ['en_diagnostic', 'en_reparation'])
            ->count();
    }
}
