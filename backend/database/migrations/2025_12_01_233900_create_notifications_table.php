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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            // Destinataire
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Type et canal
            $table->enum('type', [
                'ticket_created',
                'ticket_assigned',
                'ticket_status_changed',
                'devis_ready',
                'devis_approved',
                'devis_refused',
                'paiement_confirmed',
                'vehicle_ready',
                'rappel_rdv'
            ]);
            
            $table->enum('canal', ['email', 'sms', 'in_app'])->default('in_app');
            
            // Contenu
            $table->string('objet', 255)->nullable();
            $table->text('message');
            
            // Statut
            $table->enum('statut', ['non_lu', 'lu', 'archive'])->default('non_lu');
            $table->dateTime('date_lecture')->nullable();
            
            // Métadonnées contextuelles (ticket_id, facture_id, etc.)
            $table->json('data')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index('user_id');
            $table->index('type');
            $table->index('statut');
            $table->index(['user_id', 'statut']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

