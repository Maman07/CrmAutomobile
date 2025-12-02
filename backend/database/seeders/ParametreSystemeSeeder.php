<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParametreSysteme;

class ParametreSystemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parametres = [
            // Entreprise
            [
                'cle' => 'nom_entreprise',
                'valeur' => 'AUTOTECH SERVICES',
                'description' => 'Nom officiel de l\'entreprise',
                'type' => 'string',
            ],
            [
                'cle' => 'adresse_entreprise',
                'valeur' => 'Route de Rufisque, Dakar',
                'description' => 'Adresse physique du garage',
                'type' => 'string',
            ],
            [
                'cle' => 'telephone_entreprise',
                'valeur' => '+221 33 XXX XX XX',
                'description' => 'Téléphone fixe du garage',
                'type' => 'string',
            ],
            [
                'cle' => 'email_contact',
                'valeur' => 'contact@autotech-services.sn',
                'description' => 'Email de contact principal',
                'type' => 'string',
            ],

            // Facturation
            [
                'cle' => 'taux_tva',
                'valeur' => '18',
                'description' => 'Taux de TVA en pourcentage (Sénégal)',
                'type' => 'integer',
            ],
            [
                'cle' => 'delai_paiement_heures',
                'valeur' => '48',
                'description' => 'Délai de paiement après approbation devis (en heures)',
                'type' => 'integer',
            ],
            [
                'cle' => 'delai_validite_devis_jours',
                'valeur' => '15',
                'description' => 'Durée de validité d\'un devis (en jours)',
                'type' => 'integer',
            ],

            // Horaires
            [
                'cle' => 'horaires_ouverture',
                'valeur' => json_encode([
                    'lundi' => ['08:00', '18:00'],
                    'mardi' => ['08:00', '18:00'],
                    'mercredi' => ['08:00', '18:00'],
                    'jeudi' => ['08:00', '18:00'],
                    'vendredi' => ['08:00', '18:00'],
                    'samedi' => ['08:00', '13:00'],
                    'dimanche' => null,
                ]),
                'description' => 'Horaires d\'ouverture du garage',
                'type' => 'json',
            ],

            // Notifications
            [
                'cle' => 'notifications_email_actives',
                'valeur' => 'true',
                'description' => 'Activer les notifications par email',
                'type' => 'boolean',
            ],
            [
                'cle' => 'notifications_sms_actives',
                'valeur' => 'true',
                'description' => 'Activer les notifications par SMS',
                'type' => 'boolean',
            ],
        ];

        foreach ($parametres as $parametre) {
            ParametreSysteme::create($parametre);
        }

        $this->command->info('✅ ParametresSysteme seeded: 10 system parameters created');
    }
}
