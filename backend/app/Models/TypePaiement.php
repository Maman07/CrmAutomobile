<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePaiement extends Model
{
    use HasFactory;

    protected $table = 'types_paiement';

    protected $fillable = [
        'libelle',
        'description',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    /**
     * Paiements utilisant ce type
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    /**
     * Scope pour types actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}

