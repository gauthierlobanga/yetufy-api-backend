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
        $supportsFullText = Schema::getConnection()->getDriverName() !== 'sqlite';

        Schema::create('posts', function (Blueprint $table) use ($supportsFullText) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->enum('status', ['draft', 'published', 'scheduled', 'expired', 'archived'])->default('draft');
            $table->boolean('is_pinned')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('reading_time_minutes')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes pour les performances
            $table->index(['status', 'published_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('scheduled_for');
            $table->index('expires_at');
            if ($supportsFullText) {
                $table->fullText(['title', 'content']);
            }
        });

        // 1. D'abord la table des catégories
        Schema::create('posts_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('est_active')->default(true);
            $table->boolean('est_visible_dans_menu')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique('slug');
            $table->index(['est_active', 'ordre']);
            // $table->index('parent_id');
        });

        Schema::create('posts_categories_pivot', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('posts_categories')->cascadeOnDelete();
            $table->boolean('est_principale')->default(false);
            $table->integer('ordre')->default(0);
            $table->timestamps();
            $table->index(['post_id', 'est_principale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categorie_post_pivot');
        Schema::dropIfExists('posts_categories');
    }
};
