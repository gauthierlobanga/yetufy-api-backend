<?php

// database/migrations/xxxx_create_plans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('highlight')->nullable(); // Texte mis en avant
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('CDF');
            $table->string('interval')->default('month'); // month, year
            $table->integer('trial_days')->default(0);
            $table->string('stripe_price_id')->nullable();
            $table->string('stripe_product_id')->nullable();
            $table->jsonb('features')->nullable();
            $table->jsonb('limits')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_recommended')->default(false);
            $table->string('badge')->nullable(); // Texte du badge (ex: "Populaire")
            $table->string('badge_color')->nullable(); // Couleur du badge
            $table->string('button_text')->nullable(); // Texte personnalisé du bouton
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendor_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->nullable()->constrained('plans')->nullOnDelete();

            // Infos boutique
            $table->string('shop_name');
            $table->string('shop_slug');
            $table->text('shop_description')->nullable();

            // Contact
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();

            // Statut
            $table->string('status')->default('pending'); // pending, payment_pending, approved, rejected

            // Rejet
            $table->text('rejection_reason')->nullable();

            // Paiement
            $table->string('payment_session_id')->nullable();

            // Timestamps d'approbation/rejet
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            // Index
            $table->unique('shop_slug');
            $table->index(['user_id', 'status']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
        Schema::dropIfExists('vendor_requests');

    }
};
