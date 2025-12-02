<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            // MÉCANIQUE GÉNÉRALE
            [
                'libelle' => 'Vidange moteur complète',
                'categorie' => 'mecanique_generale',
                'description' => 'Vidange complète du moteur avec huile de qualité (5W30, 10W40 selon véhicule). Remplacement filtre à huile. Contrôle niveaux.',
                'actif' => true,
            ],
            [
                'libelle' => 'Révision système freinage',
                'categorie' => 'mecanique_generale',
                'description' => 'Contrôle complet du système de freinage : plaquettes, disques, étriers, liquide de frein.',
                'actif' => true,
            ],
            [
                'libelle' => 'Changement plaquettes freins avant',
                'categorie' => 'mecanique_generale',
                'description' => 'Remplacement des plaquettes de frein avant (jeu complet).',
                'actif' => true,
            ],
            [
                'libelle' => 'Changement plaquettes freins arrière',
                'categorie' => 'mecanique_generale',
                'description' => 'Remplacement des plaquettes de frein arrière (jeu complet).',
                'actif' => true,
            ],
            [
                'libelle' => 'Changement courroie distribution',
                'categorie' => 'mecanique_generale',
                'description' => 'Remplacement courroie de distribution + tendeurs + pompe à eau (si nécessaire).',
                'actif' => true,
            ],
            [
                'libelle' => 'Révision suspension',
                'categorie' => 'mecanique_generale',
                'description' => 'Contrôle complet de la suspension : amortisseurs, silent-blocs, rotules.',
                'actif' => true,
            ],

            // ÉLECTRICITÉ AUTOMOBILE
            [
                'libelle' => 'Diagnostic électronique complet',
                'categorie' => 'electricite_automobile',
                'description' => 'Diagnostic électronique via valise OBD : lecture codes erreurs, analyse capteurs.',
                'actif' => true,
            ],
            [
                'libelle' => 'Changement batterie',
                'categorie' => 'electricite_automobile',
                'description' => 'Remplacement de la batterie (batterie non incluse).',
                'actif' => true,
            ],
            [
                'libelle' => 'Réparation alternateur',
                'categorie' => 'electricite_automobile',
                'description' => 'Diagnostic et réparation de l\'alternateur.',
                'actif' => true,
            ],
            [
                'libelle' => 'Réparation démarreur',
                'categorie' => 'electricite_automobile',
                'description' => 'Diagnostic et réparation du démarreur.',
                'actif' => true,
            ],

            // CLIMATISATION
            [
                'libelle' => 'Recharge climatisation',
                'categorie' => 'climatisation',
                'description' => 'Recharge complète du système de climatisation avec gaz réfrigérant R134a ou R1234yf. Contrôle d\'étanchéité inclus.',
                'actif' => true,
            ],
            [
                'libelle' => 'Diagnostic panne climatisation',
                'categorie' => 'climatisation',
                'description' => 'Diagnostic complet : compresseur, détendeur, évaporateur, circuit.',
                'actif' => true,
            ],
            [
                'libelle' => 'Changement compresseur climatisation',
                'categorie' => 'climatisation',
                'description' => 'Remplacement du compresseur de climatisation (pièce non incluse).',
                'actif' => true,
            ],

            // PNEUMATIQUE
            [
                'libelle' => 'Changement 4 pneus',
                'categorie' => 'pneumatique',
                'description' => 'Montage et équilibrage de 4 pneus neufs (pneus non inclus).',
                'actif' => true,
            ],
            [
                'libelle' => 'Équilibrage des roues',
                'categorie' => 'pneumatique',
                'description' => 'Équilibrage des 4 roues.',
                'actif' => true,
            ],
            [
                'libelle' => 'Géométrie / Parallélisme',
                'categorie' => 'pneumatique',
                'description' => 'Réglage de la géométrie et du parallélisme des roues.',
                'actif' => true,
            ],

            // CARROSSERIE / PEINTURE
            [
                'libelle' => 'Débosselage léger',
                'categorie' => 'carrosserie_peinture',
                'description' => 'Réparation de petites bosses sans peinture.',
                'actif' => true,
            ],
            [
                'libelle' => 'Peinture complète véhicule',
                'categorie' => 'carrosserie_peinture',
                'description' => 'Peinture complète du véhicule (préparation + 3 couches + vernis).',
                'actif' => true,
            ],
            [
                'libelle' => 'Changement pare-brise',
                'categorie' => 'carrosserie_peinture',
                'description' => 'Remplacement du pare-brise (vitre non incluse).',
                'actif' => true,
            ],

            // DIAGNOSTIC ÉLECTRONIQUE
            [
                'libelle' => 'Reprogrammation calculateur',
                'categorie' => 'diagnostic_electronique',
                'description' => 'Reprogrammation du calculateur moteur (ECU).',
                'actif' => true,
            ],
            [
                'libelle' => 'Réparation système ABS',
                'categorie' => 'diagnostic_electronique',
                'description' => 'Diagnostic et réparation du système ABS.',
                'actif' => true,
            ],

            // ENTRETIEN COURANT
            [
                'libelle' => 'Révision complète 10 000 km',
                'categorie' => 'entretien_courant',
                'description' => 'Révision selon carnet constructeur : vidange, filtres, contrôles.',
                'actif' => true,
            ],
            [
                'libelle' => 'Révision complète 50 000 km',
                'categorie' => 'entretien_courant',
                'description' => 'Grande révision : vidange, filtres, bougies, contrôles complets.',
                'actif' => true,
            ],
            [
                'libelle' => 'Changement filtre à air',
                'categorie' => 'entretien_courant',
                'description' => 'Remplacement du filtre à air moteur.',
                'actif' => true,
            ],
            [
                'libelle' => 'Changement filtre habitacle',
                'categorie' => 'entretien_courant',
                'description' => 'Remplacement du filtre à air habitacle (pollen).',
                'actif' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        $this->command->info('✅ Services seeded: 25 services created');
    }
}
