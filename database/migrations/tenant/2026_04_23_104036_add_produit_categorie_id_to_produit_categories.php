<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('produit_categories', function (Blueprint $table) {
            $table->foreignUuid('parente_id')->nullable()->constrained('produit_categories')->nullOnDelete();
            $table->index('parente_id');
        });
    }

    public function down(): void {}
};
