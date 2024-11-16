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
        Schema::create('transferts', function (Blueprint $table) {
            $table->id();
            // Devises liées
            $table->foreignId('devise_source_id')->constrained('devises')->onDelete('cascade');
            $table->foreignId('devise_cible_id')->constrained('devises')->onDelete('cascade');
            
            // Lien vers le taux de change utilisé
            $table->foreignId('taux_echange_id')->nullable()->constrained('taux_echanges')->onDelete('set null');
            
            // Informations de transfert
            $table->decimal('montant', 15, 2);  // Montant à transférer
            $table->decimal('montant_converti', 15, 2); // Montant après conversion

            // Informations sur l'expéditeur
            $table->string('expediteur_nom');
            $table->string('expediteur_prenom');
            $table->string('expediteur_phone');
            $table->string('expediteur_email')->nullable();
            
            // Informations sur le receveur
            $table->string('receveur_nom');
            $table->string('receveur_prenom');
            $table->string('receveur_phone');
            
            // Informations supplémentaires
            $table->string('quartier');
            $table->string('code', 6)->unique();  // Code unique pour chaque transfert
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferts');
    }
};