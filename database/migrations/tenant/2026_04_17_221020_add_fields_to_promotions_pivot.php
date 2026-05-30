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
        Schema::table('promotion_client', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('promotion_panier', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('promotion_produit', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('produit_categorie_pivot', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            //
        });
    }
};
