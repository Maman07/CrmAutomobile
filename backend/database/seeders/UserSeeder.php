<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\Agent;
use App\Models\Technicien;
use App\Models\Manager;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. MANAGER (Gérant)
        $managerUser = User::create([
            'nom' => 'NDIAYE',
            'prenom' => 'Omar',
            'email' => 'manager@autotech.sn',
            'telephone' => '+221779990011',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'statut' => 'actif',
            'email_verified_at' => now(),
            'telephone_verified_at' => now(),
        ]);

        Manager::create([
            'user_id' => $managerUser->id,
            'matricule' => 'MGR-2020-001',
            'date_embauche' => '2020-01-10',
        ]);

        // 2. AGENTS (2 agents)
        $agent1User = User::create([
            'nom' => 'SOW',
            'prenom' => 'Fatou',
            'email' => 'agent1@autotech.sn',
            'telephone' => '+221765556677',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'statut' => 'actif',
            'email_verified_at' => now(),
            'telephone_verified_at' => now(),
        ]);

        Agent::create([
            'user_id' => $agent1User->id,
            'matricule' => 'AGT-2024-001',
            'date_embauche' => '2024-03-15',
            'poste' => 'Agent d\'accueil',
        ]);

        $agent2User = User::create([
            'nom' => 'KANE',
            'prenom' => 'Aminata',
            'email' => 'agent2@autotech.sn',
            'telephone' => '+221764443322',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'statut' => 'actif',
            'email_verified_at' => now(),
        ]);

        Agent::create([
            'user_id' => $agent2User->id,
            'matricule' => 'AGT-2024-002',
            'date_embauche' => '2024-06-01',
            'poste' => 'Agent d\'accueil',
        ]);

        // 3. TECHNICIENS (3 techniciens avec spécialités différentes)
        $tech1User = User::create([
            'nom' => 'DIOUF',
            'prenom' => 'Abdou',
            'email' => 'technicien1@autotech.sn',
            'telephone' => '+221773334455',
            'password' => Hash::make('password'),
            'role' => 'technicien',
            'statut' => 'actif',
            'email_verified_at' => now(),
        ]);

        Technicien::create([
            'user_id' => $tech1User->id,
            'matricule' => 'TECH-2023-001',
            'date_embauche' => '2023-01-10',
            'specialite' => 'mecanique_generale',
            'niveau_experience' => 'expert',
            'certifications' => [
                'Certification Toyota (2020)',
                'Formation freinage ABS (2022)',
            ],
        ]);

        $tech2User = User::create([
            'nom' => 'NDIAYE',
            'prenom' => 'Moussa',
            'email' => 'technicien2@autotech.sn',
            'telephone' => '+221772223344',
            'password' => Hash::make('password'),
            'role' => 'technicien',
            'statut' => 'actif',
            'email_verified_at' => now(),
        ]);

        Technicien::create([
            'user_id' => $tech2User->id,
            'matricule' => 'TECH-2023-002',
            'date_embauche' => '2023-02-20',
            'specialite' => 'electricite_automobile',
            'niveau_experience' => 'confirme',
            'certifications' => [
                'Diagnostic électronique avancé (2023)',
            ],
        ]);

        $tech3User = User::create([
            'nom' => 'SOW',
            'prenom' => 'Cheikh',
            'email' => 'technicien3@autotech.sn',
            'telephone' => '+221761112233',
            'password' => Hash::make('password'),
            'role' => 'technicien',
            'statut' => 'actif',
            'email_verified_at' => now(),
        ]);

        Technicien::create([
            'user_id' => $tech3User->id,
            'matricule' => 'TECH-2024-003',
            'date_embauche' => '2024-01-15',
            'specialite' => 'climatisation',
            'niveau_experience' => 'confirme',
            'certifications' => [],
        ]);

        // 4. CLIENTS (5 clients : 3 particuliers + 2 entreprises)
        $client1User = User::create([
            'nom' => 'DIALLO',
            'prenom' => 'Mamadou',
            'email' => 'client1@autotech.sn',
            'telephone' => '+221771234567',
            'password' => Hash::make('password'),
            'role' => 'client',
            'statut' => 'actif',
            'email_verified_at' => now(),
        ]);

        Client::create([
            'user_id' => $client1User->id,
            'adresse' => 'Sicap Liberté 6, Extension',
            'ville' => 'Dakar',
            'type_client' => 'particulier',
        ]);

        $client2User = User::create([
            'nom' => 'FALL',
            'prenom' => 'Ibrahima',
            'email' => 'client2@autotech.sn',
            'telephone' => '+221772345678',
            'password' => Hash::make('password'),
            'role' => 'client',
            'statut' => 'actif',
            'email_verified_at' => now(),
        ]);

        Client::create([
            'user_id' => $client2User->id,
            'adresse' => 'Cité Keur Gorgui',
            'ville' => 'Dakar',
            'type_client' => 'particulier',
        ]);

        $client3User = User::create([
            'nom' => 'DIOP',
            'prenom' => 'Awa',
            'email' => 'client3@autotech.sn',
            'telephone' => '+221765432109',
            'password' => Hash::make('password'),
            'role' => 'client',
            'statut' => 'actif',
            'email_verified_at' => now(),
        ]);

        Client::create([
            'user_id' => $client3User->id,
            'adresse' => 'Parcelles Assainies U10',
            'ville' => 'Dakar',
            'type_client' => 'particulier',
        ]);

        // Entreprise 1
        $clientEntreprise1User = User::create([
            'nom' => 'ENTREPRISE',
            'prenom' => 'Auto Dakar',
            'email' => 'contact@autodakar.sn',
            'telephone' => '+221338651234',
            'password' => Hash::make('password'),
            'role' => 'client',
            'statut' => 'actif',
            'email_verified_at' => now(),
        ]);

        Client::create([
            'user_id' => $clientEntreprise1User->id,
            'adresse' => 'Zone industrielle, Route de Rufisque',
            'ville' => 'Dakar',
            'type_client' => 'entreprise',
            'nom_entreprise' => 'ENTREPRISE AUTO DAKAR',
        ]);

        // Entreprise 2
        $clientEntreprise2User = User::create([
            'nom' => 'TRANSPORT',
            'prenom' => 'Sénégal Express',
            'email' => 'contact@senegalexpress.sn',
            'telephone' => '+221338759876',
            'password' => Hash::make('password'),
            'role' => 'client',
            'statut' => 'actif',
            'email_verified_at' => now(),
        ]);

        Client::create([
            'user_id' => $clientEntreprise2User->id,
            'adresse' => 'VDN, Almadies',
            'ville' => 'Dakar',
            'type_client' => 'entreprise',
            'nom_entreprise' => 'SÉNÉGAL EXPRESS TRANSPORT',
        ]);

        $this->command->info('✅ Users seeded: 1 Manager, 2 Agents, 3 Techniciens, 5 Clients');
    }
}

