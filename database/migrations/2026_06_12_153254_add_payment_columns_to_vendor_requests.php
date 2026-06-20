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
        Schema::table('vendor_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_requests', 'payment_status')) {
                $table->string('payment_status')->nullable();
            }
            if (! Schema::hasColumn('vendor_requests', 'payment_transaction_id')) {
                $table->string('payment_transaction_id')->nullable();
            }
            if (! Schema::hasColumn('vendor_requests', 'payment_failure_reason')) {
                $table->text('payment_failure_reason')->nullable();
            }
            if (! Schema::hasColumn('vendor_requests', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }
            if (! Schema::hasColumn('vendor_requests', 'reminder_sent')) {
                $table->boolean('reminder_sent')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_requests', function (Blueprint $table) {
            $table->dropColumnIfExists([
                'payment_status',
                'payment_transaction_id',
                'payment_failure_reason',
                'paid_at',
                'reminder_sent',
            ]);
        });
    }
};
