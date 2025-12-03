<?php

if (!function_exists('format_montant')) {
    /**
     * Formater un montant en FCFA
     */
    function format_montant($montant): string
    {
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }
}

if (!function_exists('format_date')) {
    /**
     * Formater une date Carbon en français
     */
    function format_date($date, string $format = 'd/m/Y'): ?string
    {
        if (!$date) {
            return null;
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format($format);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Formater une date-heure Carbon
     */
    function format_datetime($datetime, string $format = 'd/m/Y H:i'): ?string
    {
        if (!$datetime) {
            return null;
        }

        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }

        return $datetime->format($format);
    }
}

if (!function_exists('generer_numero_unique')) {
    /**
     * Générer un numéro unique (ticket, devis, facture)
     */
    function generer_numero_unique(string $prefix, string $modelClass): string
    {
        $annee = date('Y');
        $dernier = $modelClass::whereYear('created_at', $annee)->count() + 1;
        return sprintf('%s-%s-%03d', $prefix, $annee, $dernier);
    }
}

if (!function_exists('calculer_tva')) {
    /**
     * Calculer la TVA (18% au Sénégal)
     */
    function calculer_tva(float $montantHT, float $tauxTVA = 18): float
    {
        return round($montantHT * ($tauxTVA / 100), 2);
    }
}

if (!function_exists('calculer_ttc')) {
    /**
     * Calculer le montant TTC
     */
    function calculer_ttc(float $montantHT, float $tauxTVA = 18): float
    {
        return round($montantHT + calculer_tva($montantHT, $tauxTVA), 2);
    }
}

if (!function_exists('format_telephone_senegal')) {
    /**
     * Formater un numéro de téléphone sénégalais
     */
    function format_telephone_senegal(string $telephone): string
    {
        // Supprimer espaces et tirets
        $clean = preg_replace('/[^0-9+]/', '', $telephone);

        // Si commence par 77, 78, 76, 70, 75 → ajouter +221
        if (preg_match('/^(77|78|76|70|75)/', $clean)) {
            $clean = '+221' . $clean;
        }

        return $clean;
    }
}

if (!function_exists('get_statut_badge_class')) {
    /**
     * Retourner la classe CSS selon le statut (pour frontend)
     */
    function get_statut_badge_class(string $statut): string
    {
        return match ($statut) {
            'en_attente' => 'warning',
            'en_diagnostic' => 'info',
            'devis_envoye' => 'primary',
            'devis_approuve' => 'success',
            'en_reparation' => 'info',
            'repare' => 'success',
            'livre' => 'success',
            'cloture' => 'secondary',
            'annule' => 'danger',
            default => 'secondary',
        };
    }
}

if (!function_exists('get_priorite_badge_class')) {
    /**
     * Classe CSS selon priorité
     */
    function get_priorite_badge_class(string $priorite): string
    {
        return match ($priorite) {
            'basse' => 'secondary',
            'normale' => 'primary',
            'haute' => 'warning',
            'urgente' => 'danger',
            default => 'secondary',
        };
    }
}

if (!function_exists('est_mobile_money')) {
    /**
     * Vérifier si un type de paiement est mobile money
     */
    function est_mobile_money(string $typePaiement): bool
    {
        return in_array($typePaiement, ['Wave', 'Orange Money', 'Free Money']);
    }
}

if (!function_exists('get_user_avatar_url')) {
    /**
     * URL avatar utilisateur (Gravatar par défaut)
     */
    function get_user_avatar_url(string $email, int $size = 200): string
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
    }
}

if (!function_exists('duree_en_heures_minutes')) {
    /**
     * Convertir durée en minutes vers format "Xh Ymin"
     */
    function duree_en_heures_minutes(int $minutes): string
    {
        $heures = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($heures > 0 && $mins > 0) {
            return "{$heures}h {$mins}min";
        } elseif ($heures > 0) {
            return "{$heures}h";
        } else {
            return "{$mins}min";
        }
    }
}

if (!function_exists('age_vehicule')) {
    /**
     * Calculer l'âge d'un véhicule
     */
    function age_vehicule(int $annee): int
    {
        return date('Y') - $annee;
    }
}

if (!function_exists('est_email_verifie')) {
    /**
     * Vérifier si email vérifié
     */
    function est_email_verifie($user): bool
    {
        return !is_null($user->email_verified_at);
    }
}

if (!function_exists('est_telephone_verifie')) {
    /**
     * Vérifier si téléphone vérifié
     */
    function est_telephone_verifie($user): bool
    {
        return !is_null($user->telephone_verified_at);
    }
}

