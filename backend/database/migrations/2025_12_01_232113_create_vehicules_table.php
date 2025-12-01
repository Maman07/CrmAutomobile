<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicules', function (Blueprint $table) {
            $table->id();
            
            // Relation avec clients (1-N)
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->onDelete('cascade');
            
            // Identification du véhicule
            $table->string('immatriculation', 20)->unique();
            $table->string('marque', 100);
            $table->string('modele', 100);
            $table->year('annee');
            $table->string('couleur', 50)->nullable();
            $table->string('numero_serie', 50)->unique()->nullable();
            
            // Caractéristiques techniques
            $table->enum('type_carburant', ['essence', 'diesel', 'hybride', 'electrique']);
            $table->integer('capacite_reservoir')->nullable()->comment('En litres');
            $table->decimal('consommation_moyenne', 4, 2)->nullable()->comment('L/100km');
            
            // Kilométrage
            $table->integer('dernier_kilometrage')->nullable();
            $table->date('date_kilometrage')->nullable();
            
            // Métadonnées
            $table->date('date_ajout')->default(DB::raw('CURRENT_DATE'));
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('client_id');
            $table->index('immatriculation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicules');
    }
};
