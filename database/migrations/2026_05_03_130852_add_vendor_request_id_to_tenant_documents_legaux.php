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
        Schema::table('tenant_documents_legaux', function (Blueprint $table) {
            $table->uuid('vendor_request_id')->nullable()->after('id');
            $table->foreign('vendor_request_id')->references('id')->on('vendor_requests')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_documents_legaux', function (Blueprint $table) {
            //
        });
    }
};
