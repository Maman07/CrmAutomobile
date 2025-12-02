<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ParametreSysteme extends Model
{
    use HasFactory;

    protected $table = 'parametres_systeme';

    protected $fillable = [
        'cle',
        'valeur',
        'description',
        'type',
    ];

    /**
     * Récupérer la valeur d'un paramètre (avec cache)
     */
    public static function get(string $cle, $default = null)
    {
        return Cache::remember("parametre_{$cle}", 3600, function () use ($cle, $default) {
            $parametre = self::where('cle', $cle)->first();
            
            if (!$parametre) {
                return $default;
            }

            return self::castValue($parametre->valeur, $parametre->type);
        });
    }

    /**
     * Définir la valeur d'un paramètre
     */
    public static function set(string $cle, $valeur, string $type = 'string', ?string $description = null): void
    {
        self::updateOrCreate(
            ['cle' => $cle],
            [
                'valeur' => is_array($valeur) ? json_encode($valeur) : $valeur,
                'type' => $type,
                'description' => $description,
            ]
        );

        // Invalider le cache
        Cache::forget("parametre_{$cle}");
    }

    /**
     * Caster la valeur selon son type
     */
    protected static function castValue($valeur, string $type)
    {
        return match ($type) {
            'integer' => (int) $valeur,
            'boolean' => filter_var($valeur, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($valeur, true),
            'date' => \Carbon\Carbon::parse($valeur),
            default => $valeur,
        };
    }

    /**
     * Vider le cache de tous les paramètres
     */
    public static function clearCache(): void
    {
        self::all()->each(function ($parametre) {
            Cache::forget("parametre_{$parametre->cle}");
        });
    }
}

