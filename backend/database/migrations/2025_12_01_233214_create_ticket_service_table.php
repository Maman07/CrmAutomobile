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
        Schema::create('ticket_service', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('ticket_intervention_id')
                  ->constrained('tickets_intervention')
                  ->onDelete('cascade');
                  
            $table->foreignId('service_id')
                  ->constrained('services')
                  ->onDelete('cascade');
            
            $table->timestamps();
            
            // Contrainte unique pour éviter doublons
            $table->unique(
                ['ticket_intervention_id', 'service_id'], 
                'ticket_service_unique'
            );
            
            // Index pour requêtes
            $table->index('ticket_intervention_id');
            $table->index('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_service');
    }
};
