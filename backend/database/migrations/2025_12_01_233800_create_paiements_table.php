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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('facture_id')
                  ->constrained('factures')
                  ->onDelete('cascade');
                  
            $table->foreignId('type_paiement_id')
                  ->constrained('types_paiement')
                  ->onDelete('restrict');
            
            // Montant
            $table->decimal('montant', 10, 2);
            
            // Informations
            $table->dateTime('date_paiement');
            $table->enum('statut', ['en_attente', 'confirme', 'echoue', 'annule'])
                  ->default('en_attente');
            
            // Références externes (API paiement mobile)
            $table->string('reference_externe', 255)
                  ->nullable()
                  ->comment('ID transaction API paiement mobile');
            
            // Métadonnées
            $table->json('metadata')
                  ->nullable()
                  ->comment('Réponse complète API paiement');
            
            $table->timestamps();
            
            // Index
            $table->index('facture_id');
            $table->index('type_paiement_id');
            $table->index('statut');
            $table->index('date_paiement');
            $table->index('reference_externe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};

