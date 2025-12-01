<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('techniciens', function (Blueprint $table) {
            $table->id();
            
            // Relation avec users (1-1)
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Informations professionnelles
            $table->string('matricule', 50)->unique();
            $table->date('date_embauche');
            
            // Spécialité et niveau
            $table->enum('specialite', [
                'mecanique_generale',
                'electricite_automobile',
                'carrosserie_peinture',
                'pneumatique',
                'climatisation',
                'diagnostic_electronique'
            ]);
            
            $table->enum('niveau_experience', ['debutant', 'confirme', 'expert'])
                  ->default('confirme');
            
            // Certifications (JSON)
            $table->json('certifications')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index('user_id');
            $table->index('matricule');
            $table->index('specialite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('techniciens');
    }
};
