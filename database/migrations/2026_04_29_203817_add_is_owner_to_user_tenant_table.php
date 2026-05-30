<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema::table('user_tenant', function (Blueprint $table) {
        //     if (! Schema::hasColumn('user_tenant', 'is_owner')) {
        //         $table->boolean('is_owner')->default(false);
        //     }

        // });
    }

    public function down(): void
    {
        // Schema::table('user_tenant', function (Blueprint $table) {
        //     $table->dropColumn('is_owner');
        // });
    }
};
