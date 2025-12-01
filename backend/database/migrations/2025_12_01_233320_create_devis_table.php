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
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('ticket_intervention_id')
                  ->constrained('tickets_intervention')
                  ->onDelete('cascade');
                  
            $table->foreignId('technicien_id')
                  ->nullable()
                  ->constrained('techniciens')
                  ->onDelete('set null');
            
            // Identification
            $table->string('numero', 50)->unique();
            
            // Description
            $table->text('description')->nullable();
            
            // Montants
            $table->decimal('montant_ht', 10, 2)->default(0);
            $table->decimal('montant_tva', 10, 2)->default(0);
            $table->decimal('montant_ttc', 10, 2)->default(0);
            
            // Statut
            $table->enum('statut', ['en_attente', 'approuve', 'refuse', 'expire'])
                  ->default('en_attente');
            
            // Dates
            $table->date('date_validite')->nullable();
            $table->dateTime('date_approbation')->nullable();
            $table->text('motif_refus')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('ticket_intervention_id');
            $table->index('technicien_id');
            $table->index('numero');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};
