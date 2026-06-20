<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_requests', function (Blueprint $table) {
            $table->dropUnique('vendor_requests_shop_slug_unique');

            if (! Schema::hasColumn('vendor_requests', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('payment_session_id');
            }

            if (! Schema::hasColumn('vendor_requests', 'payment_transaction_id')) {
                $table->string('payment_transaction_id')->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('vendor_requests', 'payment_failure_reason')) {
                $table->text('payment_failure_reason')->nullable()->after('payment_transaction_id');
            }

            if (! Schema::hasColumn('vendor_requests', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_failure_reason');
            }

            if (! Schema::hasColumn('vendor_requests', 'reminder_sent')) {
                $table->boolean('reminder_sent')->default(false)->after('paid_at');
            }

            $table->index('shop_slug');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_requests', function (Blueprint $table) {
            $table->dropIndex(['shop_slug']);
            $table->dropColumnIfExists([
                'payment_status',
                'payment_transaction_id',
                'payment_failure_reason',
                'paid_at',
                'reminder_sent',
            ]);
            $table->unique('shop_slug');
        });
    }
};
