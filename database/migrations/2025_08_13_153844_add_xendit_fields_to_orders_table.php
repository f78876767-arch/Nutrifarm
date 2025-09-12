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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders','external_id')) {
                $table->string('external_id')->nullable()->after('invoice_no');
            }
            if (!Schema::hasColumn('orders','xendit_invoice_id')) {
                $table->string('xendit_invoice_id')->nullable()->after('external_id');
            }
            if (!Schema::hasColumn('orders','xendit_invoice_url')) {
                $table->string('xendit_invoice_url')->nullable()->after('xendit_invoice_id');
            }
            if (!Schema::hasColumn('orders','payment_status')) {
                $table->string('payment_status')->default('pending')->after('status');
            }
            if (!Schema::hasColumn('orders','paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders','external_id')) $table->dropColumn('external_id');
            if (Schema::hasColumn('orders','xendit_invoice_id')) $table->dropColumn('xendit_invoice_id');
            if (Schema::hasColumn('orders','xendit_invoice_url')) $table->dropColumn('xendit_invoice_url');
            if (Schema::hasColumn('orders','paid_at')) $table->dropColumn('paid_at');
            // keep payment_status maybe
        });
    }
};
