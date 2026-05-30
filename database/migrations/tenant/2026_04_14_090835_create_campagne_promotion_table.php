<?php

// database/migrations/2024_01_01_000000_create_campagne_promotion_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campagne_promotion', function (Blueprint $table) {
            $table->foreignUuid('campagne_marketing_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('promotion_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['campagne_marketing_id', 'promotion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campagne_promotion');
    }
};
