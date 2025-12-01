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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Qui ? (utilisateur peut être supprimé, on garde la trace)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            // Quoi ?
            $table->string('action', 100);
            $table->string('entity_type', 100)
                  ->nullable()
                  ->comment('Nom du Model (ex: App\Models\Ticket)');
            $table->unsignedBigInteger('entity_id')
                  ->nullable()
                  ->comment('ID de l\'entité modifiée');
            
            // Détails
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Contexte
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index('user_id');
            $table->index('action');
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

