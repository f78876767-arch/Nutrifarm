<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $prefix = 'NUT-';
            $last = DB::table('orders')
                ->whereNotNull('invoice_no')
                ->where('invoice_no', 'like', $prefix.'%')
                ->orderByDesc('id')
                ->value('invoice_no');

            $next = 1;
            if ($last) {
                $num = (int) preg_replace('/\D/', '', (string) $last);
                $next = $num + 1;
            }

            // Backfill missing invoice_no sequentially by ID
            DB::table('orders')
                ->whereNull('invoice_no')
                ->orderBy('id')
                ->chunkById(200, function ($orders) use (&$next, $prefix) {
                    foreach ($orders as $o) {
                        $code = $prefix . str_pad((string) $next++, 6, '0', STR_PAD_LEFT);
                        DB::table('orders')->where('id', $o->id)->update(['invoice_no' => $code]);
                    }
                });

            // Only add unique index if all non-null values are unique
            $hasDuplicates = DB::table('orders')
                ->select('invoice_no', DB::raw('COUNT(*) as c'))
                ->whereNotNull('invoice_no')
                ->groupBy('invoice_no')
                ->having('c', '>', 1)
                ->exists();

            if (!$hasDuplicates) {
                Schema::table('orders', function (Blueprint $table) {
                    // Ensure column exists and is long enough
                    if (!Schema::hasColumn('orders', 'invoice_no')) {
                        $table->string('invoice_no')->nullable()->after('id');
                    }
                    $table->unique('invoice_no');
                });
            }
        });
    }

    public function down(): void
    {
        // Drop unique index if exists
        Schema::table('orders', function (Blueprint $table) {
            try {
                $table->dropUnique(['invoice_no']);
            } catch (Throwable $e) {
                // Fallback to named index if needed
                try { $table->dropUnique('orders_invoice_no_unique'); } catch (Throwable $e2) {}
            }
        });
    }
};
