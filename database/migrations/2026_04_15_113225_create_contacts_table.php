<?php

// database/migrations/2024_01_01_000001_create_contacts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('email');
            $table->string('telephone')->nullable();
            $table->string('sujet');
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->enum('status', ['en_attente', 'lu', 'repondu', 'archive', 'spam'])->default('en_attente'); //
            $table->enum('priorite', ['basse', 'moyenne', 'haute', 'urgente'])->default('moyenne'); //
            $table->enum('categorie', ['general', 'commercial', 'technique', 'support', 'reclamation'])->default('general'); //
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('reponse')->nullable();
            $table->foreignUuid('repondu_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('lu_at')->nullable();
            $table->timestamp('repondu_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'status']);
            $table->index('status');
            $table->index('priorite');
            $table->index('categorie');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
