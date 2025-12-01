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
        Schema::create('types_paiement', function (Blueprint $table) {
            $table->id();
            
            // Informations
            $table->string('libelle', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            
            $table->timestamps();
            
            // Index
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_paiement');
    }
};

