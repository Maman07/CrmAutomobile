<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypePaiement;

class TypePaiementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'libelle' => 'Wave',
                'description' => 'Paiement mobile via Wave (Sénégal). Confirmation automatique via API.',
                'actif' => true,
            ],
            [
                'libelle' => 'Orange Money',
                'description' => 'Paiement mobile via Orange Money. Confirmation automatique via API.',
                'actif' => true,
            ],
            [
                'libelle' => 'Free Money',
                'description' => 'Paiement mobile via Free Money. Confirmation automatique via API.',
                'actif' => true,
            ],
            [
                'libelle' => 'Virement bancaire',
                'description' => 'Virement bancaire sur compte professionnel. Justificatif requis. Vérification comptable.',
                'actif' => true,
            ],
            [
                'libelle' => 'Chèque',
                'description' => 'Paiement par chèque (entreprises uniquement). Justificatif requis. Vérification comptable.',
                'actif' => true,
            ],
        ];

        foreach ($types as $type) {
            TypePaiement::create($type);
        }

        $this->command->info('✅ TypesPaiement seeded: 5 payment methods created');
    }
}

