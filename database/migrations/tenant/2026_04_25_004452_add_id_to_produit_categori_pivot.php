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

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignUuid('parrain_id')->nullable()->constrained('clients')->nullOnDelete();
        });

        // Schema::table('posts_categories', function (Blueprint $table) {
        //     $table->foreignUuid('parent_id')->nullable()->constrained('posts_categories')->cascadeOnDelete();
        // });

        Schema::table('comments', function (Blueprint $table) {
            $table->foreignUuid('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
