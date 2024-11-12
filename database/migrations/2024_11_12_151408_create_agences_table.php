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
        Schema::create('agences', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // Référence unique
            $table->string('nom');
            $table->string('phone');
            $table->string('email')->unique();
            $table->foreignId('adresse_id')->constrained('adresses')->onDelete('cascade');
            $table->date('date_creation'); // Date de création
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};
