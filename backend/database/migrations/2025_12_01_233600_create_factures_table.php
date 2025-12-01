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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('devis_id')
                  ->constrained('devis')
                  ->onDelete('cascade');
                  
            $table->foreignId('ticket_intervention_id')
                  ->constrained('tickets_intervention')
                  ->onDelete('cascade');
            
            // Identification
            $table->string('numero', 50)->unique();
            
            // Montants (copiés depuis devis)
            $table->decimal('montant_ht', 10, 2)->default(0);
            $table->decimal('montant_tva', 10, 2)->default(0);
            $table->decimal('montant_ttc', 10, 2)->default(0);
            
            // Statut
            $table->enum('statut', ['en_attente', 'payee', 'annulee'])
                  ->default('en_attente');
            
            // Dates
            $table->date('date_emission');
            $table->date('date_echeance')->nullable();
            $table->dateTime('date_paiement')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('devis_id');
            $table->index('ticket_intervention_id');
            $table->index('numero');
            $table->index('statut');
            $table->index('date_emission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};

