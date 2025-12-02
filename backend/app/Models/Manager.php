<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'matricule',
        'date_embauche',
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
}
