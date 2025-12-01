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
        Schema::create('parametres_systeme', function (Blueprint $table) {
            $table->id();
            
            // Clé-valeur
            $table->string('cle', 100)->unique();
            $table->text('valeur')->nullable();
            $table->text('description')->nullable();
            
            // Type pour validation
            $table->enum('type', ['string', 'integer', 'boolean', 'json', 'date'])
                  ->default('string');
            
            $table->timestamps();
            
            // Index
            $table->index('cle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres_systeme');
    }
};

