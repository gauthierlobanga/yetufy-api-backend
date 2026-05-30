<?php

// database/migrations/xxxx_create_legal_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Table des types de documents légaux
        Schema::create('type_documents_legaux', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('autorite_emettrice')->nullable();
            $table->boolean('est_obligatoire')->default(true);
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        // Table pivot : documents du tenant
        Schema::create('tenant_documents_legaux', function (Blueprint $table) {
            $table->uuid('id')->primary();
        $table->string('tenant_id');
        $table->foreign('tenant_id')
            ->references('id')->on('tenants')
            ->onDelete('cascade');
            $table->foreignUuid('type_document_id')->constrained('type_documents_legaux')->cascadeOnDelete();
            $table->string('numero_document')->nullable();
            $table->date('date_delivrance')->nullable();
            $table->date('date_expiration')->nullable();
            $table->string('lieu_delivrance')->nullable();
            $table->string('autorite_delivrance')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->boolean('est_verifie')->default(false);
            $table->timestamp('verifie_le')->nullable();
            $table->foreignUuid('verifie_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'type_document_id']);
        });

        // Insérer les types de documents congolais
        $this->seedTypeDocuments();
    }

    private function seedTypeDocuments(): void
    {
        $types = [
            [
                'code' => 'RCCM',
                'nom' => 'Registre du Commerce et du Crédit Mobilier',
                'description' => 'Numéro d\'immatriculation au registre du commerce',
                'autorite_emettrice' => 'Guichet Unique de Création d\'Entreprise (GUCE)',
                'est_obligatoire' => true,
                'ordre' => 1,
            ],
            [
                'code' => 'PATENTE',
                'nom' => 'Patente commerciale',
                'description' => 'Autorisation annuelle d\'exercer une activité commerciale',
                'autorite_emettrice' => 'Direction Générale des Recettes (DGR)',
                'est_obligatoire' => true,
                'ordre' => 2,
            ],
            [
                'code' => 'IFU',
                'nom' => 'Identifiant Fiscal Unique (Numéro Impôt)',
                'description' => 'Numéro d\'identification fiscale',
                'autorite_emettrice' => 'Direction Générale des Impôts (DGI)',
                'est_obligatoire' => true,
                'ordre' => 3,
            ],
            [
                'code' => 'ID_NAT',
                'nom' => 'Identification Nationale',
                'description' => 'Carte d\'identité nationale congolaise',
                'autorite_emettrice' => 'Office National de l\'Identification de la Population (ONIP)',
                'est_obligatoire' => true,
                'ordre' => 4,
            ],
            [
                'code' => 'STATUTS',
                'nom' => 'Statuts notariés',
                'description' => 'Acte de création de la société',
                'autorite_emettrice' => 'Notaire / Tribunal de Commerce',
                'est_obligatoire' => true,
                'ordre' => 5,
            ],
            [
                'code' => 'PERSONNALITE_JURIDIQUE',
                'nom' => 'Personnalité juridique',
                'description' => 'Document de reconnaissance légale (pour ONG, ASBL)',
                'autorite_emettrice' => 'Ministère de la Justice',
                'est_obligatoire' => false,
                'ordre' => 6,
            ],
            [
                'code' => 'TPE',
                'nom' => 'Taxe Professionnelle sur les Entreprises',
                'description' => 'Attestation de paiement de la TPE',
                'autorite_emettrice' => 'DGRAD / DGI',
                'est_obligatoire' => false,
                'ordre' => 7,
            ],
            [
                'code' => 'AUTORISATION_FONCTIONNEMENT',
                'nom' => 'Autorisation de fonctionnement',
                'description' => 'Autorisation spécifique selon le secteur d\'activité',
                'autorite_emettrice' => 'Ministère sectoriel compétent',
                'est_obligatoire' => false,
                'ordre' => 8,
            ],
            [
                'code' => 'ATTESTATION_FISCALE',
                'nom' => 'Attestation de situation fiscale',
                'description' => 'Attestation de régularité fiscale',
                'autorite_emettrice' => 'DGI',
                'est_obligatoire' => false,
                'ordre' => 9,
            ],
            [
                'code' => 'CARTE_ARTISAN',
                'nom' => 'Carte d\'artisan',
                'description' => 'Carte professionnelle d\'artisan',
                'autorite_emettrice' => 'Ministère des PME/Artisanat',
                'est_obligatoire' => true,
                'ordre' => 10,
            ],
        ];

        foreach ($types as $type) {
            DB::table('type_documents_legaux')->insert(array_merge($type, [
                'id' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_documents_legaux');
        Schema::dropIfExists('type_documents_legaux');
    }
};
