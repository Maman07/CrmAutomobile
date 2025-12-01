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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            
            // Relation avec users (1-1)
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Informations spécifiques
            $table->string('adresse', 255)->nullable();
            $table->string('ville', 100)->nullable();
            $table->enum('type_client', ['particulier', 'entreprise'])
                  ->default('particulier');
            $table->string('nom_entreprise', 255)->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index('user_id');
            $table->index('type_client');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
