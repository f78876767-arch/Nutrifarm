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
            // Make legacy pricing fields nullable since they are now in variants
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->integer('stock_quantity')->nullable()->change();
            $table->string('sku', 100)->nullable()->change();
            $table->decimal('discount_amount', 10, 2)->nullable()->change();
            $table->decimal('discount_price', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert back to NOT NULL (be careful with existing data)
            $table->decimal('price', 10, 2)->nullable(false)->change();
            $table->integer('stock_quantity')->nullable(false)->change();
            $table->string('sku', 100)->nullable(false)->change();
            $table->decimal('discount_amount', 10, 2)->nullable(false)->change();
            $table->decimal('discount_price', 10, 2)->nullable(false)->change();
        });
    }
};
