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
                'libelle' => 'Espèces',
                'description' => 'Paiement en espèces au comptoir.',
                'actif' => true,
            ],
            [
                'libelle' => 'Wave',
                'description' => 'Paiement mobile via Wave (Sénégal).',
                'actif' => true,
            ],
            [
                'libelle' => 'Orange Money',
                'description' => 'Paiement mobile via Orange Money.',
                'actif' => true,
            ],
            [
                'libelle' => 'Virement bancaire',
                'description' => 'Virement bancaire sur compte professionnel.',
                'actif' => true,
            ],
            [
                'libelle' => 'Chèque',
                'description' => 'Paiement par chèque (entreprises uniquement).',
                'actif' => true,
            ],
        ];

        foreach ($types as $type) {
            TypePaiement::create($type);
        }

        $this->command->info('✅ TypesPaiement seeded: 5 payment methods created');
    }
}

