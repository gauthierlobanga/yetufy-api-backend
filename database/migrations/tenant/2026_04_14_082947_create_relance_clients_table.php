<?php

// database/migrations/2024_01_01_000001_create_support_tickets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // public function up(): void
    // {
    //     Schema::create('tickets', function (Blueprint $table) {
    //         $table->uuid('id')->primary();
    //         $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
    //         $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
    //         $table->string('categorie');
    //         $table->enum('priorite', ['basse', 'moyenne', 'haute', 'urgente'])->default('moyenne');
    //         $table->string('sujet');
    //         $table->text('contenu');
    //         $table->string('reference')->unique();
    //         $table->enum('statut', ['ouvert', 'en_cours', 'en_attente', 'resolu', 'ferme'])->default('ouvert');
    //         $table->string('ip_address')->nullable();
    //         $table->text('user_agent')->nullable();
    //         $table->timestamp('closed_at')->nullable();
    //         $table->timestamp('resolved_at')->nullable();
    //         $table->json('metadata')->nullable();
    //         $table->timestamps();
    //         $table->softDeletes();

    //         $table->index(['client_id', 'statut']);
    //         $table->index('reference');
    //         $table->index('categorie');
    //         $table->index('priorite');
    //     });

    //     Schema::create('ticket_messages', function (Blueprint $table) {
    //         $table->id();
    //         $table->foreignUuid('ticket_id')->constrained()->cascadeOnDelete();
    //         $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    //         $table->text('contenu');
    //         $table->boolean('is_internal')->default(false);
    //         $table->json('attachments')->nullable();
    //         $table->timestamps();
    //     });

    //     Schema::create('devis', function (Blueprint $table) {
    //         $table->uuid('id')->primary();
    //         $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
    //         $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
    //         $table->foreignUuid('adresse_facturation_id')->nullable()->constrained('adresses')->nullOnDelete();
    //         $table->foreignUuid('adresse_livraison_id')->nullable()->constrained('adresses')->nullOnDelete();
    //         $table->string('reference')->unique();
    //         $table->enum('statut', ['brouillon', 'envoye', 'accepte', 'refuse', 'expire', 'transforme'])->default('brouillon');
    //         $table->decimal('sous_total', 12, 2);
    //         $table->decimal('taxe', 12, 2)->default(0);
    //         $table->decimal('remise', 12, 2)->default(0);
    //         $table->decimal('total', 12, 2);
    //         $table->foreignUuid('devise_id')->nullable()->constrained()->nullOnDelete();
    //         $table->decimal('taux_change', 10, 4)->default(1);
    //         $table->text('notes')->nullable();
    //         $table->jsonb('conditions')->nullable();
    //         $table->timestamp('date_validite')->nullable();
    //         $table->timestamp('date_envoi')->nullable();
    //         $table->timestamp('date_acceptation')->nullable();
    //         $table->timestamp('date_rejet')->nullable();
    //         $table->foreignUuid('commande_id')->nullable()->constrained()->nullOnDelete();
    //         $table->jsonb('metadata')->nullable();
    //         $table->timestamps();
    //         $table->softDeletes();

    //         $table->index(['client_id', 'statut']);
    //         $table->index('reference');
    //         $table->index('date_validite');
    //     });

    //     Schema::create('ligne_devis', function (Blueprint $table) {
    //         $table->uuid('id')->primary();
    //         $table->foreignUuid('devis_id')->constrained()->cascadeOnDelete();
    //         $table->foreignUuid('produit_id')->constrained()->cascadeOnDelete();
    //         $table->foreignUuid('variante_produit_id')->nullable()->constrained()->nullOnDelete();
    //         $table->integer('quantite');
    //         $table->decimal('prix_unitaire', 12, 2);
    //         $table->decimal('prix_total', 12, 2);
    //         $table->decimal('taxe', 12, 2)->default(0);
    //         $table->decimal('remise', 12, 2)->default(0);
    //         $table->jsonb('options')->nullable();
    //         $table->timestamps();
    //     });

    //     Schema::create('factures', function (Blueprint $table) {
    //         $table->id();
    //         $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
    //         $table->foreignUuid('commande_id')->nullable()->constrained()->nullOnDelete();
    //         $table->foreignUuid('devis_id')->nullable()->constrained()->nullOnDelete();
    //         $table->string('reference')->unique();
    //         $table->enum('statut', ['en_attente', 'payee', 'en_retard', 'annulee', 'remboursee'])->default('en_attente');
    //         $table->decimal('sous_total', 12, 2);
    //         $table->decimal('taxe', 12, 2)->default(0);
    //         $table->decimal('remise', 12, 2)->default(0);
    //         $table->decimal('total', 12, 2);
    //         $table->foreignUuid('devise_id')->nullable()->constrained()->nullOnDelete();
    //         $table->decimal('taux_change', 10, 4)->default(1);
    //         $table->text('notes')->nullable();
    //         $table->timestamp('date_emission')->useCurrent();
    //         $table->timestamp('date_echeance')->nullable();
    //         $table->timestamp('date_paiement')->nullable();
    //         $table->json('metadata')->nullable();
    //         $table->timestamps();
    //         $table->softDeletes();

    //         $table->index(['client_id', 'statut']);
    //         $table->index('reference');
    //         $table->index('date_echeance');
    //     });

    //     Schema::create('relances_clients', function (Blueprint $table) {
    //         $table->id();
    //         $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
    //         $table->string('type');
    //         $table->string('sujet');
    //         $table->text('contenu');
    //         $table->enum('statut', ['en_attente', 'envoye', 'ouvert', 'clique', 'converti', 'echec'])->default('en_attente');
    //         $table->timestamp('date_envoi')->nullable();
    //         $table->timestamp('date_ouverture')->nullable();
    //         $table->timestamp('date_clic')->nullable();
    //         $table->jsonb('metadata')->nullable();
    //         $table->timestamps();

    //         $table->index(['client_id', 'type', 'statut']);
    //         $table->index('statut');
    //     });
    // }

    // public function down(): void
    // {
    //     Schema::dropIfExists('tickets');
    //     Schema::dropIfExists('relances_clients');
    //     Schema::dropIfExists('ligne_devis');
    //     Schema::dropIfExists('devis');
    //     Schema::dropIfExists('ticket_messages');
    //     Schema::dropIfExists('factures');
    // }
};
