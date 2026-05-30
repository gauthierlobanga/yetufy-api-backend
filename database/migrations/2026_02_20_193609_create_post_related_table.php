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
        Schema::create('post_related', function (Blueprint $table) {
            $table->foreignUuid('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();
            $table->foreignUuid('related_post_id')
                ->constrained('posts')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['post_id', 'related_post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_related');
    }
};
