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
        Schema::create('lignes_devis', function (Blueprint $table) {
            $table->id();
            
            // Relation avec devis
            $table->foreignId('devis_id')
                  ->constrained('devis')
                  ->onDelete('cascade');
            
            // Détails de la ligne
            $table->string('designation', 255);
            $table->enum('type', ['main_oeuvre', 'piece', 'fourniture'])
                  ->default('piece');
            $table->integer('quantite')->default(1);
            $table->decimal('prix_unitaire', 10, 2)->default(0);
            $table->decimal('montant', 10, 2)->default(0);
            
            // Ordre d'affichage
            $table->integer('ordre')->default(0);
            
            $table->timestamps();
            
            // Index
            $table->index('devis_id');
            $table->index(['devis_id', 'ordre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lignes_devis');
    }
};

