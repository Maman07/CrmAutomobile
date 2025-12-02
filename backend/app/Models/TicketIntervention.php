<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketIntervention extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tickets_intervention';

    protected $fillable = [
        'client_id',
        'vehicule_id',
        'technicien_id',
        'numero_ticket',
        'description',
        'statut',
        'priorite',
        'kilometrage_entree',
        'kilometrage_sortie',
        'date_rdv',
        'date_affectation',
        'date_cloture',
        'duree_estimee',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'kilometrage_entree' => 'integer',
            'kilometrage_sortie' => 'integer',
            'duree_estimee' => 'integer',
            'date_rdv' => 'datetime',
            'date_affectation' => 'datetime',
            'date_cloture' => 'datetime',
        ];
    }

    /**
     * Relation avec Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation avec Vehicule
     */
    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class);
    }

    /**
     * Relation avec Technicien
     */
    public function technicien()
    {
        return $this->belongsTo(Technicien::class);
    }

    /**
     * Relation Many-to-Many avec Services
     */
    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'ticket_service',
            'ticket_intervention_id',
            'service_id'
        )->withTimestamps();
    }

    /**
     * Devis associé
     */
    public function devis()
    {
        return $this->hasOne(Devis::class);
    }

    /**
     * Facture associée
     */
    public function facture()
    {
        return $this->hasOne(Facture::class);
    }

    /**
     * Vérifier si ticket est cloturé
     */
    public function isCloture(): bool
    {
        return in_array($this->statut, ['cloture', 'annule', 'livre']);
    }

    /**
     * Affecter à un technicien
     */
    public function affecterTechnicien(int $technicienId): void
    {
        $this->update([
            'technicien_id' => $technicienId,
            'date_affectation' => now(),
            'statut' => 'en_diagnostic',
        ]);
    }

    /**
     * Changer le statut
     */
    public function changerStatut(string $nouveauStatut): void
    {
        $this->update(['statut' => $nouveauStatut]);

        if (in_array($nouveauStatut, ['cloture', 'livre'])) {
            $this->update(['date_cloture' => now()]);
        }
    }

    /**
     * Scope pour tickets en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour tickets d'un technicien
     */
    public function scopeTechnicien($query, int $technicienId)
    {
        return $query->where('technicien_id', $technicienId);
    }

    /**
     * Scope pour tickets par statut
     */
    public function scopeStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }
}
