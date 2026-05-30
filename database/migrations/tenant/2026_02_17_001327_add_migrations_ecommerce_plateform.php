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

        Schema::create('adresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('rue');
            $table->string('complement')->nullable();
            $table->string('code_postal');
            $table->string('ville');
            $table->string('pays');
            $table->string('region')->nullable();
            $table->string('telephone')->nullable();
            $table->text('instructions')->nullable();
            $table->uuidMorphs('addressable'); // pour client, etc.
            $table->enum('type', ['facturation', 'livraison'])->default('livraison');
            $table->boolean('est_defaut')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('type', ['particulier', 'professionnel', 'entreprise'])->default('particulier'); // particulier, professionnel
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('civilite')->nullable();
            $table->string('societe')->nullable();
            $table->string('siret')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('portable')->nullable();
            $table->string('fax')->nullable();
            $table->string('code_tva')->nullable();
            $table->string('site_web')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('date_derniere_connexion')->nullable();
            $table->decimal('total_remises', 10, 2)->default(0);
            $table->decimal('chiffre_affaire', 10, 2)->default(0);
            $table->integer('points_fidelite')->default(0);
            $table->string('niveau_fidelite')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->boolean('est_actif')->default(true);
            $table->string('source')->nullable();
            $table->jsonb('preferences')->nullable();
            $table->timestamp('date_premier_achat')->nullable();
            $table->timestamp('date_dernier_achat')->nullable();
            $table->decimal('total_achats', 10, 2)->default(0);
            $table->integer('nombre_commandes')->default(0);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('produit_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->string('color')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->boolean('est_active')->default(true);
            $table->boolean('is_featured')->default(true)->after('est_active');
            $table->boolean('show_in_menu')->default(true)->after('is_featured');
            $table->string('short_description')->after('description');
            $table->string('seo_title')->after('short_description');
            $table->string('seo_description')->after('seo_title');
            $table->jsonb('seo_keywords')->after('seo_description');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['est_active', 'order']);
        });

        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->string('siret')->nullable();
            $table->string('code_tva')->nullable();
            $table->jsonb('conditions')->nullable();
            $table->jsonb('coordonnees_bancaires')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->integer('delai_livraison_jours')->default(7);
            $table->decimal('frais_port_min', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nom');
            $table->index('email');
            $table->index('est_actif');
        });

        Schema::create('taxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('code');
            $table->decimal('taux', 5, 2);
            $table->string('pays')->nullable();
            $table->string('region')->nullable();
            $table->boolean('est_defaut')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('devises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 3); // EUR, USD
            $table->string('symbole', 5);
            $table->decimal('taux_change', 10, 4)->default(1); // par rapport à devise de référence
            $table->boolean('est_reference')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('brands', function (Blueprint $table) use ($supportsFullText) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->jsonb('seo')->nullable();
            $table->jsonb('social_links')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('sort_order');
            if ($supportsFullText) {
                $table->fullText(['name', 'description']);
            }
        });

        Schema::create('produits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('devise_id')->nullable()->constrained('devises')->nullOnDelete();
            $table->foreignUuid('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->string('reference')->nullable();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->text('description_longue')->nullable();
            $table->decimal('prix_ht', 10, 2);
            $table->decimal('prix_ttc', 10, 2);
            $table->decimal('prix_promotion', 10, 2)->nullable();
            $table->integer('quantite_stock')->default(0);
            $table->integer('seuil_alerte')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('ean')->nullable();
            $table->decimal('poids', 8, 2)->nullable(); // en kg
            $table->decimal('hauteur', 8, 2)->nullable(); // en cm
            $table->decimal('largeur', 8, 2)->nullable();
            $table->decimal('profondeur', 8, 2)->nullable();
            $table->string('unite_mesure')->nullable();
            $table->jsonb('attributs')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->jsonb('attributes')->nullable();
            $table->enum('statut', ['brouillon', 'publie', 'desactive', 'en_rupture', 'discontinued'])->default('brouillon');
            $table->integer('vues')->default(0);
            $table->boolean('is_featured')->default(true)->after('statut');
            $table->boolean('is_new')->default(true)->after('is_featured');
            $table->boolean('is_bestseller')->default(true)->after('is_new');
            $table->integer('views_count')->default(0)->after('is_bestseller');
            $table->integer('sold_count')->default(0)->after('views_count');
            $table->decimal('average_rating', 10, 2)->default(0)->after('sold_count');
            $table->integer('reviews_count')->default(0)->after('average_rating');
            $table->string('seo_title')->after('short_description');
            $table->string('seo_description')->after('seo_title');
            $table->jsonb('seo_keywords')->after('seo_description');
            $table->timestamp('published_at')->nullable()->after('seo_keywords');
            $table->timestamp('scheduled_for')->nullable()->after('published_at');
            $table->timestamp('expires_at')->nullable()->after('scheduled_for');
            $table->timestamp('date_publication')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('variante_produits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('order')->default(0);
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->string('nom');
            $table->string('valeur');
            $table->decimal('supplement_prix', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->string('sku_variante')->nullable()->unique();
            $table->jsonb('attributs')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('entrepots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->text('adresse');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('est_principal')->default(false);
            $table->jsonb('configuration')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entrepot_id')->references('id')->on('entrepots')->onDelete('cascade');
            $table->string('reference')->unique();
            $table->string('statut')->default('en_cours');
            $table->jsonb('resultats')->nullable(); // écarts par produit
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mouvement_stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->foreignUuid('entrepot_id')->references('id')->on('entrepots')->onDelete('cascade');
            $table->foreignUuid('inventaire_id')->references('id')->on('inventaires')->onDelete('cascade');
            $table->enum('type', ['entree', 'sortie', 'ajustement', 'transfert'])->default('entree');
            $table->integer('quantite'); // positif ou négatif
            $table->string('reference')->nullable(); // ex: numéro commande
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('date_mouvement')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('paniers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('session_id')->nullable()->index();
            $table->enum('statut', ['actif', 'converti', 'abandonne'])->default('actif');
            $table->decimal('sous_total', 10, 2)->default(0);
            $table->decimal('total_taxes', 10, 2)->default(0);
            $table->decimal('total_livraison', 10, 2)->default(0);
            $table->decimal('total_remises', 10, 2)->default(0);
            $table->decimal('total_general', 10, 2)->default(0);
            $table->jsonb('metadata')->nullable();
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->nullable();
            $table->timestamp('date_abandon')->nullable();
            $table->timestamp('date_conversion')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('item_paniers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('panier_id')->references('id')->on('paniers')->onDelete('cascade');
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->foreignUuid('variante_produit_id')->references('id')->on('variante_produits')->onDelete('cascade');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('prix_total', 10, 2);
            $table->decimal('taxe_unitaire', 10, 2)->default(0);
            $table->decimal('remise_unitaire', 10, 2)->default(0);
            $table->jsonb('options_selectionnees')->nullable();
            $table->jsonb('personnalisation')->nullable();
            $table->timestamp('added_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('livraison_paniers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('panier_id')->references('id')->on('paniers')->onDelete('cascade');
            $table->foreignUuid('adresse_id')->references('id')->on('adresses')->onDelete('cascade');
            $table->string('mode');
            $table->decimal('cout', 10, 2);
            $table->timestamp('date_estimee')->nullable();
            $table->jsonb('options')->nullable();
            $table->timestamp('selected_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('regle_paniers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUUid('panier_id')->references('id')->on('paniers')->onDelete('cascade');
            $table->enum('type', ['remise_pourcentage', 'remise_montant', 'livraison_offerte', 'produit_offert', 'code_promo'])->default('livraison_offerte');
            $table->string('code')->nullable();
            $table->jsonb('conditions')->nullable();
            $table->decimal('valeur', 10, 2)->nullable();
            $table->boolean('appliquee')->default(false);
            $table->jsonb('resultat')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('abandon_paniers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('panier_id')->references('id')->on('paniers')->onDelete('cascade');
            $table->string('raison')->nullable();
            $table->string('etape_abandon')->nullable();
            $table->integer('nombre_relances')->default(0);
            $table->timestamp('derniere_relance')->nullable();
            $table->boolean('recupere')->default(false);
            $table->timestamp('date_recuperation')->nullable();
            $table->jsonb('analytics_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('relance_paniers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('abandon_panier_id')->references('id')->on('abandon_paniers')->onDelete('cascade');
            $table->enum('canal', ['email', 'sms', 'push', 'notification'])->default('email'); //
            $table->enum('statut', ['envoye', 'ouvert', 'clique', 'converti', 'echec'])->default('envoye');
            $table->decimal('taux_conversion', 5, 2)->nullable();
            $table->jsonb('contenu')->nullable();
            $table->timestamp('envoye_at')->nullable();
            $table->timestamp('ouvert_at')->nullable();
            $table->timestamp('clique_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commandes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreignUuid('panier_id')->references('id')->on('paniers')->onDelete('cascade');
            $table->foreignUuid('adresse_facturation_id')->references('id')->on('adresses')->onDelete('cascade');
            $table->foreignUuid('adresse_livraison_id')->references('id')->on('adresses')->onDelete('cascade');
            $table->string('numero_commande')->unique();
            $table->enum('statut', [
                'en_attente',
                'payee',
                'en_preparation',
                'expediee',
                'livree',
                'annulee',
                'remboursee',
                'echec_paiement',
            ])->default('en_attente');
            $table->decimal('sous_total', 10, 2);
            $table->decimal('taxe', 10, 2)->default(0);
            $table->decimal('frais_livraison', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('mode_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('date_commande')->useCurrent();
            $table->timestamp('date_paiement')->nullable();
            $table->timestamp('date_expedition')->nullable();
            $table->timestamp('date_livraison')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ligne_commandes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('commande_id')->references('id')->on('commandes')->onDelete('cascade');
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->foreignUuid('variante_produit_id')->references('id')->on('variante_produits')->onDelete('cascade');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('prix_total', 10, 2);
            $table->decimal('taxe', 10, 2)->default(0);
            $table->decimal('remise', 10, 2)->default(0);
            $table->jsonb('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('commande_id')->constrained('commandes')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('transaction_id')->nullable()->unique();
            $table->enum('mode', ['carte', 'paypal', 'virement', 'cheque', 'especes', 'crypto', 'mobile_money']);
            $table->string('carte_brand')->nullable();
            $table->string('carte_last4')->nullable();
            $table->decimal('montant', 12, 2);
            $table->string('devise', 3)->default('EUR');
            $table->enum('statut', ['en_attente', 'valide', 'echec', 'rembourse', 'partiel'])->default('en_attente');
            $table->jsonb('details')->nullable();
            $table->timestamp('date_paiement')->nullable();
            $table->timestamp('date_remboursement')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commande_id', 'statut']);
            $table->index('transaction_id');
        });

        Schema::create('retours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('commande_id')->constrained('commandes')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->enum('motif', [
                'defectueux',
                'mauvaise_taille',
                'ne_correspond_pas',
                'erreur_commande',
                'produit_abime',
                'livraison_tardive',
                'autre',
            ]);
            $table->text('motif_autre')->nullable();
            $table->enum('statut', ['en_attente', 'accepte', 'refuse', 'en_cours', 'termine'])->default('en_attente');
            $table->enum('action', ['remboursement', 'avoir', 'echange'])->default('remboursement');
            $table->text('commentaire')->nullable();
            $table->jsonb('documents')->nullable();
            $table->timestamp('date_demande')->useCurrent();
            $table->timestamp('date_traitement')->nullable();
            $table->timestamp('date_recuperation')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commande_id', 'statut']);
            $table->index('reference');
        });

        Schema::create('remboursements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('paiement_id')->constrained('paiements')->cascadeOnDelete();
            $table->foreignUuid('retour_id')->nullable()->constrained('retours')->nullOnDelete();
            $table->string('reference')->unique();
            $table->decimal('montant', 12, 2);
            $table->enum('mode', ['carte', 'paypal', 'virement', 'avoir', 'especes']);
            $table->enum('statut', ['en_attente', 'valide', 'echec'])->default('en_attente');
            $table->string('motif')->nullable();
            $table->jsonb('details')->nullable();
            $table->timestamp('date_remboursement')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['paiement_id', 'statut']);
        });

        Schema::create('ligne_retours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('retour_id')->constrained('retours')->cascadeOnDelete();
            $table->foreignUuid('ligne_commande_id')->constrained('ligne_commandes')->cascadeOnDelete();
            $table->integer('quantite');
            $table->decimal('montant', 12, 2);
            $table->enum('etat', ['conforme', 'defectueux', 'endommage', 'incomplet'])->default('conforme');
            $table->text('commentaire')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['retour_id', 'ligne_commande_id']);
        });

        /** Pour la platefom */
        Schema::create('commande_achats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fournisseur_id')->references('id')->on('fournisseurs')->onDelete('cascade');
            $table->timestamp('date_commande')->useCurrent();
            $table->string('numero_commande')->unique();
            $table->date('date_livraison_prevue')->nullable();
            $table->date('date_livraison_reelle')->nullable();
            $table->enum('statut', [
                'brouillon',
                'envoyee',
                'confirmee',
                'expediee',
                'recue_partielle',
                'recue_totale',
                'annulee',
            ])->default('brouillon');
            $table->decimal('sous_total_ht', 12, 2)->default(0);
            $table->decimal('remise', 10, 2)->default(0);
            $table->decimal('frais_livraison', 10, 2)->default(0);
            $table->decimal('taxe', 10, 2)->default(0);
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('date_commande');
        });

        Schema::create('ligne_commande_achats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('commande_achat')->constrained('commande_achats')->cascadeOnDelete();
            $table->foreignUuid('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->integer('quantite');
            $table->integer('quantite_recue')->default(0);
            $table->decimal('prix_unitaire_ht', 12, 2);
            $table->decimal('total_ht', 12, 2);
            $table->decimal('tva', 5, 2)->default(0);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('produit_id');
        });

        Schema::create('produit_fournisseur', function (Blueprint $table) {
            $table->foreignUuid('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignUuid('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
            $table->decimal('prix_achat_ht', 12, 2)->nullable();
            $table->integer('delai_approvisionnement_jours')->nullable();
            $table->string('reference_fournisseur')->nullable();
            $table->boolean('est_principal')->default(false);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->primary(['produit_id', 'fournisseur_id']);
            $table->index('fournisseur_id');
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('code')->nullable()->unique();
            $table->enum('type', ['pourcentage', 'montant_fixe', 'livraison_offerte', 'achat_unique'])->default('montant_fixe');
            $table->decimal('valeur', 10, 2);
            $table->decimal('minimum_panier', 10, 2)->nullable();
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->integer('utilisation_max')->nullable();
            $table->integer('utilisation_courante')->default(0);
            $table->boolean('cumulable')->default(false);
            $table->jsonb('produits_cibles')->nullable(); // ou relation many-to-many
            $table->jsonb('metadata')->nullable();
            $table->string('nom')->nullable()->after('id');
            $table->text('description')->nullable()->after('nom');
            $table->boolean('est_active')->default(true)->after('cumulable');
            $table->jsonb('coupons')->nullable()->after('metadata');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('campagne_marketings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->enum('type', ['newsletter', 'promotion', 'saisonniere', 'relance'])->default('newsletter');
            $table->enum('canal', ['email', 'sms', 'reseaux', 'push'])->default('email');
            $table->enum('statut', ['planifiee', 'active', 'terminee', 'annulee'])->default('planifiee');
            $table->jsonb('cible')->nullable();
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->jsonb('statistiques')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('promotion_produit', function (Blueprint $table) {
            $table->foreignUuid('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->decimal('valeur_specifique', 10, 2)->nullable();
            $table->primary(['promotion_id', 'produit_id']);
        });

        Schema::create('promotion_client', function (Blueprint $table) {
            $table->foreignUuid('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreignUuid('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->integer('utilisations')->default(0);
            $table->timestamp('premiere_utilisation')->nullable();
            $table->timestamp('derniere_utilisation')->nullable();

            $table->primary(['promotion_id', 'client_id']);
        });

        Schema::create('promotion_panier', function (Blueprint $table) {
            $table->foreignUuid('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreignUuid('panier_id')->references('id')->on('paniers')->onDelete('cascade');
            $table->decimal('montant_applique', 10, 2);
            $table->timestamp('applied_at')->useCurrent();
            $table->primary(['promotion_id', 'panier_id']);
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->string('nom')->nullable();
            $table->boolean('est_publique')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wishlist_id')->references('id')->on('wishlists')->onDelete('cascade');
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->integer('quantite')->default(1);
            $table->timestamp('added_at')->useCurrent();
        });

        Schema::create('programme_fidelites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->enum('type', ['points', 'paliers'])->default('points'); // points, paliers
            $table->jsonb('regles'); // ex: {"gain": "1 point par euro", "seuils": [...]}
            $table->jsonb('recompenses')->nullable();
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->timestamps();
            $table->SoftDeletes();
        });

        Schema::create('compte_fidelites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreignUuid('programme_fidelite_id')->nullable()->constrained('programme_fidelites')->nullOnDelete();
            $table->integer('points')->default(0);
            $table->integer('points_cumules')->default(0);
            $table->string('niveau')->nullable();
            $table->timestamp('derniere_maj')->nullable();
            $table->timestamps();
            $table->SoftDeletes();
        });

        Schema::create('transaction_fidelites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('compte_fidelite_id')->references('id')->on('compte_fidelites')->onDelete('cascade');
            $table->enum('type', ['gain', 'utilisation', 'expiration'])->default('gain'); //
            $table->integer('points');
            $table->string('raison')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('date_transaction')->useCurrent();
        });

        Schema::create('notification_commandes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('commande_id')->references('id')->on('commandes')->onDelete('cascade');
            $table->uuidMorphs('notifiable');
            $table->enum('type', ['email', 'sms', 'push'])->default('email');
            $table->string('sujet')->nullable();
            $table->text('contenu');
            $table->enum('statut', ['en_attente', 'envoye', 'echec', 'lu'])->default('en_attente');
            $table->jsonb('metadata')->nullable();
            $table->timestamp('date_envoi')->nullable();
            $table->timestamp('date_lecture')->nullable();
            $table->timestamps();
            $table->SoftDeletes();
        });

        Schema::create('avis_clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->integer('note');
            $table->text('commentaire')->nullable();
            $table->text('reponse')->nullable();
            $table->boolean('approuve')->default(false);
            $table->timestamp('date_avis')->useCurrent();
            $table->timestamps();
            $table->SoftDeletes();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('action', ['CREATE', 'UPDATE', 'DELETE'])->default('CREATE');
            $table->string('entite_type');
            $table->uuid('entite_id');
            $table->jsonb('anciennes_valeurs')->nullable();
            $table->jsonb('nouvelles_valeurs')->nullable();
            $table->ipAddress()->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->SoftDeletes();
            $table->index(['entite_type', 'entite_id']);
        });

        Schema::create('plan_abonnements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->decimal('prix_ht', 10, 2);
            $table->decimal('prix_ttc', 10, 2);
            $table->enum('periodicite', ['mois', 'an'])->default('mois');
            $table->jsonb('caracteristiques')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
            $table->SoftDeletes();
        });

        Schema::create('abonnements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreignUuid('plan_abonnement_id')->references('id')->on('plan_abonnements')->onDelete('cascade');
            $table->enum('statut', ['actif', 'suspendu', 'resilie', 'expire'])->default('actif');
            $table->timestamp('date_debut')->useCurrent();
            $table->timestamp('date_prochaine_facture')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->SoftDeletes();
        });

        Schema::create('facture_abonnements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('abonnement_id')->references('id')->on('abonnements')->onDelete('cascade');
            $table->string('reference')->unique();
            $table->decimal('montant', 10, 2);
            $table->timestamp('date_echeance');
            $table->timestamp('date_paiement')->nullable();
            $table->enum('statut', ['en_attente', 'termine', 'envoye'])->default('en_attente');
            $table->timestamps();
            $table->SoftDeletes();
        });

        Schema::create('traductions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('langue', 5); // fr, en, es
            $table->string('entite_type'); // Produit, Categorie, Promotion
            $table->uuid('entite_id');
            $table->string('champ'); // nom, description
            $table->text('valeur');
            $table->timestamps();
            $table->SoftDeletes();
            $table->index(['entite_type', 'entite_id', 'langue']);
        });

        // ==================== 17. TABLES PIVOTS ====================

        Schema::create('produit_categorie_pivot', function (Blueprint $table) {
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->foreignUuid('category_id')->references('id')->on('produit_categories')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->integer('order')->default(0);
            $table->index(['is_primary']);
            $table->primary(['produit_id', 'category_id']);
        });

        Schema::create('produit_entrepot', function (Blueprint $table) {
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->foreignUuid('entrepot_id')->references('id')->on('entrepots')->onDelete('cascade');
            $table->integer('quantite')->default(0);
            $table->integer('quantite_reservee')->default(0);
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
            $table->primary(['produit_id', 'entrepot_id']);
        });

        Schema::create('produit_taxe', function (Blueprint $table) {
            $table->foreignUuid('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignUuid('taxe_id')->constrained('taxes')->cascadeOnDelete();
            $table->primary(['produit_id', 'taxe_id']);
        });

        Schema::create('produit_prix_par_devises', function (Blueprint $table) {
            $table->foreignUuid('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignUuid('devise_id')->constrained('devises')->cascadeOnDelete();
            $table->decimal('prix_ht', 10, 2);
            $table->decimal('prix_ttc', 10, 2);
            $table->timestamps();
            $table->primary(['produit_id', 'devise_id']);
        });

        // ==================== 13. TABLE STATISTIQUES ====================

        Schema::create('statistiques', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['ventes_jour', 'top_produits']);
            $table->jsonb('donnees');
            $table->date('date_reference');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'date_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adresses');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('produit_categories');
        Schema::dropIfExists('fournisseurs');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('devises');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('produits');
        Schema::dropIfExists('produit_taxe');
        Schema::dropIfExists('variante_produits');
        Schema::dropIfExists('entrepots');
        Schema::dropIfExists('produit_entrepot');
        Schema::dropIfExists('inventaires');
        Schema::dropIfExists('mouvement_stocks');
        Schema::dropIfExists('paniers');
        Schema::dropIfExists('item_paniers');
        Schema::dropIfExists('livraison_paniers');
        Schema::dropIfExists('regle_paniers');
        Schema::dropIfExists('abandon_paniers');
        Schema::dropIfExists('relance_paniers');
        Schema::dropIfExists('commandes');
        Schema::dropIfExists('ligne_commandes');
        Schema::dropIfExists('retours');
        Schema::dropIfExists('remboursements');
        Schema::dropIfExists('ligne_retours');
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('commande_achats');
        Schema::dropIfExists('ligne_commande_achats');
        Schema::dropIfExists('produit_fournisseur');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('campagne_marketings');
        Schema::dropIfExists('promotion_produit');
        Schema::dropIfExists('promotion_client');
        Schema::dropIfExists('promotion_panier');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('programme_fidelites');
        Schema::dropIfExists('compte_fidelites');
        Schema::dropIfExists('transaction_fidelites');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('statistiques');
        Schema::dropIfExists('avis_clients');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('plan_abonnements');
        Schema::dropIfExists('abonnements');
        Schema::dropIfExists('facture_abonnements');
        Schema::dropIfExists('traductions');
        // Tables pivots
        Schema::dropIfExists('produit_prix_par_devises');
        Schema::dropIfExists('produit_taxe');
        Schema::dropIfExists('produit_entrepot');
        Schema::dropIfExists('produit_categorie_pivot');
    }
};
