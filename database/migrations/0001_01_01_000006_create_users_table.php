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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('reference', 5)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->date('date_naissance')->default('9999-12-31');
            $table->enum('civilite', ['Mr', 'Mme', 'Mlle', 'Autre'])->default('Autre');
            $table->foreignId('adresse_id')->constrained('adresses')->onDelete('cascade');
            $table->enum('statut', ['active', 'attente', 'bloque', 'archive'])->default('attente');
            $table->string('nom');
            $table->string('prenom');
            $table->string('phone');
            $table->string('password'); 
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};