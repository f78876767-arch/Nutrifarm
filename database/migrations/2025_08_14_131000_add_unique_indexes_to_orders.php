<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'external_id')) {
                // Drop non-unique index if we created it earlier
                try { $table->dropIndex('idx_orders_external_id'); } catch (\Throwable $e) {}
                // Add unique index (allows multiple NULLs in MySQL)
                $table->unique('external_id', 'uniq_orders_external_id');
            }
            if (Schema::hasColumn('orders', 'xendit_invoice_id')) {
                $table->unique('xendit_invoice_id', 'uniq_orders_xendit_invoice_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            try { $table->dropUnique('uniq_orders_external_id'); } catch (\Throwable $e) {}
            try { $table->dropUnique('uniq_orders_xendit_invoice_id'); } catch (\Throwable $e) {}
            // Recreate non-unique index for external_id to keep query performance if needed
            if (Schema::hasColumn('orders', 'external_id')) {
                try { $table->index('external_id', 'idx_orders_external_id'); } catch (\Throwable $e) {}
            }
        });
    }
};
