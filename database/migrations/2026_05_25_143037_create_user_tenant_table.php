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
        if (! Schema::hasTable('user_tenant')) {
            Schema::create('user_tenant', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->string('tenant_id');
                $table->uuid('user_id');

                $table->boolean('is_owner')->default(false);

                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_tenant', function (Blueprint $table) {
            //
        });
    }
};
