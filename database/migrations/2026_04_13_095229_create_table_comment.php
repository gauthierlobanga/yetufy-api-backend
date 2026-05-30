<?php

// database/migrations/2024_01_01_000001_create_comments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->jsonb('metadata')->nullable();
            $table->enum('status', ['pending', 'approved', 'spam', 'trashed'])->default('pending');
            $table->integer('likes_count')->default(0);
            $table->integer('dislikes_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->integer('reports_count')->default(0);
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('content');
        });

        Schema::create('comment_likes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignuuid('comment_id')->constrained()->cascadeOnDelete();
            $table->foreignuuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['like', 'dislike'])->default('like');
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
            $table->index(['comment_id', 'type']);
        });

        Schema::create('comment_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignuuid('comment_id')->constrained()->cascadeOnDelete();
            $table->foreignuuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('comment_mentions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('comment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('comment_likes');
        Schema::dropIfExists('comment_reports');
        Schema::dropIfExists('comment_mentions');

    }
};
