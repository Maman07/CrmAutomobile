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
        Schema::create('tickets_intervention', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->onDelete('cascade');
                  
            $table->foreignId('vehicule_id')
                  ->constrained('vehicules')
                  ->onDelete('cascade');
                  
            $table->foreignId('technicien_id')
                  ->nullable()
                  ->constrained('techniciens')
                  ->onDelete('set null');
            
            // Identification
            $table->string('numero_ticket', 50)->unique();
            
            // Description
            $table->text('description');
            
            // Statut et priorité
            $table->enum('statut', [
                'en_attente',
                'en_diagnostic',
                'devis_envoye',
                'devis_approuve',
                'en_reparation',
                'repare',
                'livre',
                'cloture',
                'annule'
            ])->default('en_attente');
            
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])
                  ->default('normale');
            
            // Kilométrage
            $table->integer('kilometrage_entree')->nullable();
            $table->integer('kilometrage_sortie')->nullable();
            
            // Dates et durées
            $table->dateTime('date_rdv')->nullable();
            $table->dateTime('date_affectation')->nullable();
            $table->dateTime('date_cloture')->nullable();
            $table->integer('duree_estimee')->nullable()->comment('En minutes');
            
            // Observations
            $table->text('observation')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index (table très consultée)
            $table->index('client_id');
            $table->index('vehicule_id');
            $table->index('technicien_id');
            $table->index('numero_ticket');
            $table->index('statut');
            $table->index('date_rdv');
            $table->index(['statut', 'technicien_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets_intervention');
    }
};
