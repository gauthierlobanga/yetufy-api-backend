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
        Schema::create('post_likes', function (Blueprint $table) {
            $table->id(); // SERIAL -> bigIncrements
            $table->uuid('post_id');
            $table->uuid('user_id');
            $table->timestamps();

            // Clés étrangères avec les noms de contraintes du schéma original
            $table->foreign('post_id')
                ->references('id')
                ->on('posts')
                ->name('post_likes_post_id_foreign');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->name('post_likes_user_id_foreign');

            // Index unique sur (post_id, user_id)
            $table->unique(['post_id', 'user_id'], 'post_likes_post_id_user_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_likes');
    }
};
