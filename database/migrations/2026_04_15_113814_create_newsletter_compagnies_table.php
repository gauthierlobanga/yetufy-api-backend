<?php

// database/migrations/2024_01_01_000003_create_newsletter_campaigns_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('titre');
            $table->string('sujet');
            $table->text('contenu_html');
            $table->text('contenu_text')->nullable();
            $table->jsonb('segments_cibles')->nullable(); // catégories, clients, etc.
            $table->enum('status', ['brouillon', 'programme', 'envoye', 'annule'])->default('brouillon');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('total_envoyes')->default(0);
            $table->integer('total_ouverts')->default(0);
            $table->integer('total_clics')->default(0);
            $table->integer('total_desabonnements')->default(0);
            $table->jsonb('statistiques')->nullable();
            $table->foreignUuid('cree_par')->constrained('users')->cascadeOnDelete();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('scheduled_at');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_campaigns');
    }
};
