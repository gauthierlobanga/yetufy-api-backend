<?php

// database/migrations/2024_01_01_000002_create_newsletters_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('prenom')->nullable();
            $table->string('nom')->nullable();
            $table->jsonb('preferences')->nullable(); // categories d'intérêt, fréquence
            $table->string('token_confirmation')->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->enum('source', ['formulaire', 'checkout', 'compte', 'import'])->default('formulaire'); //
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'is_active']);
            $table->index('confirmed_at');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletters');
    }
};
