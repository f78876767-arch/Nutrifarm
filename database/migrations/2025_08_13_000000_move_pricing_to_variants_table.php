<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add pricing & discount fields to variants
        Schema::table('variants', function (Blueprint $table) {
            if (!Schema::hasColumn('variants', 'sku')) {
                $table->string('sku')->nullable()->unique()->after('value');
            }
            if (!Schema::hasColumn('variants', 'base_price')) {
                $table->decimal('base_price', 12, 2)->default(0)->after('sku');
            }
            if (!Schema::hasColumn('variants', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->nullable()->after('base_price');
            }
            if (!Schema::hasColumn('variants', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('variants', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('stock_quantity');
            }
            if (!Schema::hasColumn('variants', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->dropColumn(['sku', 'base_price', 'discount_amount', 'stock_quantity', 'is_active', 'weight']);
        });
    }
};
