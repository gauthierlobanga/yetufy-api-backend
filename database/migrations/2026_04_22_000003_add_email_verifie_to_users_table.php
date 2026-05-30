<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users') || Schema::hasColumn('users', 'email_verifie')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_verifie')->default(false)->after('email_verified_at');
        });

        DB::table('users')
            ->whereNotNull('email_verified_at')
            ->update(['email_verifie' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'email_verifie')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verifie');
        });
    }
};
