<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_sends', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('newsletter_campaigns')->cascadeOnDelete();
            $table->foreignUuid('newsletter_id')->constrained('newsletters')->cascadeOnDelete();
            $table->string('email');
            $table->enum('status', ['envoye', 'ouvert', 'clique', 'erreur', 'desabonne'])->default('envoye');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'newsletter_id']);
            $table->index(['campaign_id', 'status']);
            $table->index('opened_at');
            $table->index('clicked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_sends');
    }
};
