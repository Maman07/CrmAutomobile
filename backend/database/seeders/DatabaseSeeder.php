<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ordre important : respecter les foreign keys
        $this->call([
            // 1. Users (table parent)
            UserSeeder::class,
            
            // 2. Services (pas de dépendances)
            ServiceSeeder::class,
            
            // 3. Types de paiement (pas de dépendances)
            TypePaiementSeeder::class,
            
            // 4. Paramètres système (pas de dépendances)
            ParametreSystemeSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('📊 Données créées :');
        $this->command->info('   - 1 Manager');
        $this->command->info('   - 2 Agents');
        $this->command->info('   - 3 Techniciens');
        $this->command->info('   - 5 Clients');
        $this->command->info('   - 25 Services');
        $this->command->info('   - 5 Types de paiement');
        $this->command->info('   - 10 Paramètres système');
        $this->command->info('');
        $this->command->info('🔐 Connexions par défaut :');
        $this->command->info('   Manager    : manager@autotech.sn / password');
        $this->command->info('   Agent      : agent1@autotech.sn / password');
        $this->command->info('   Technicien : technicien1@autotech.sn / password');
        $this->command->info('   Client     : client1@autotech.sn / password');
    }
}
