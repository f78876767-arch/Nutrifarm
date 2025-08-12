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
        Schema::table('products', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (Schema::hasColumn('products', 'stock') && !Schema::hasColumn('products', 'stock_quantity')) {
                $table->renameColumn('stock', 'stock_quantity');
            }
            if (Schema::hasColumn('products', 'image') && !Schema::hasColumn('products', 'image_path')) {
                $table->renameColumn('image', 'image_path');
            }
            if (Schema::hasColumn('products', 'active') && !Schema::hasColumn('products', 'is_active')) {
                $table->renameColumn('active', 'is_active');
            }
            if (!Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 100)->nullable()->unique()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'stock_quantity') && !Schema::hasColumn('products', 'stock')) {
                $table->renameColumn('stock_quantity', 'stock');
            }
            if (Schema::hasColumn('products', 'image_path') && !Schema::hasColumn('products', 'image')) {
                $table->renameColumn('image_path', 'image');
            }
            if (Schema::hasColumn('products', 'is_active') && !Schema::hasColumn('products', 'active')) {
                $table->renameColumn('is_active', 'active');
            }
            if (Schema::hasColumn('products', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropColumn('sku');
            }
        });
    }
};
