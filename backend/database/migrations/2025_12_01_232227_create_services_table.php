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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            
            // Informations du service
            $table->string('libelle', 255);
            
            $table->enum('categorie', [
                'mecanique_generale',
                'electricite_automobile',
                'carrosserie_peinture',
                'pneumatique',
                'climatisation',
                'diagnostic_electronique',
                'entretien_courant'
            ]);
            
            $table->text('description')->nullable();
            
            // Statut
            $table->boolean('actif')->default(true);
            
            $table->timestamps();
            
            // Index
            $table->index('categorie');
            $table->index('actif');
            $table->index(['categorie', 'actif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
