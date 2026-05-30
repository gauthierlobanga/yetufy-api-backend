<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('raison_sociale')->nullable()->unique();
            $table->string('slug')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->enum('statut', ['actif', 'inactif', 'en_attente', 'suspendu'])->default('actif');
            $table->boolean('is_active')->default(true);
            $table->jsonb('configuration')->nullable();
            $table->jsonb('data')->nullable();
            $table->timestamp('date_activation')->nullable();
            $table->timestamp('date_expiration')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
