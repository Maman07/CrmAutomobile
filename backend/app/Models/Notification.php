<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'canal',
        'objet',
        'message',
        'statut',
        'date_lecture',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'date_lecture' => 'datetime',
            'data' => 'array',
        ];
    }

    /**
     * Relation avec User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marquer comme lu
     */
    public function marquerCommeLu(): void
    {
        if ($this->statut === 'non_lu') {
            $this->update([
                'statut' => 'lu',
                'date_lecture' => now(),
            ]);
        }
    }

    /**
     * Archiver la notification
     */
    public function archiver(): void
    {
        $this->update(['statut' => 'archive']);
    }

    /**
     * Vérifier si la notification est lue
     */
    public function isLu(): bool
    {
        return $this->statut === 'lu';
    }

    /**
     * Créer une notification
     */
    public static function creer(
        int $userId,
        string $type,
        string $message,
        array $data = [],
        string $canal = 'in_app'
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'canal' => $canal,
            'message' => $message,
            'data' => $data,
            'statut' => 'non_lu',
        ]);
    }

    /**
     * Scope pour notifications non lues
     */
    public function scopeNonLues($query)
    {
        return $query->where('statut', 'non_lu');
    }

    /**
     * Scope pour notifications d'un utilisateur
     */
    public function scopeUtilisateur($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour notifications par type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour notifications récentes
     */
    public function scopeRecentes($query, int $jours = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($jours));
    }
}

