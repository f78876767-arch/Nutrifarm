<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Orders indexes
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'created_at')) {
                    $table->index('created_at', 'idx_orders_created_at');
                }
                if (Schema::hasColumn('orders', 'status')) {
                    $table->index('status', 'idx_orders_status');
                }
                if (Schema::hasColumn('orders', 'payment_status')) {
                    $table->index('payment_status', 'idx_orders_payment_status');
                }
                if (Schema::hasColumn('orders', 'external_id')) {
                    $table->index('external_id', 'idx_orders_external_id');
                }
                if (Schema::hasColumn('orders', 'invoice_no')) {
                    $table->index('invoice_no', 'idx_orders_invoice_no');
                }
                if (Schema::hasColumn('orders', 'resi')) {
                    $table->index('resi', 'idx_orders_resi');
                }
                if (Schema::hasColumn('orders', 'user_id')) {
                    $table->index('user_id', 'idx_orders_user_id');
                }
            });
        }

        // Users indexes
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'name')) {
                    $table->index('name', 'idx_users_name');
                }
                if (Schema::hasColumn('users', 'created_at')) {
                    $table->index('created_at', 'idx_users_created_at');
                }
            });
        }

        // Products indexes
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'name')) {
                    $table->index('name', 'idx_products_name');
                }
                if (Schema::hasColumn('products', 'sku')) {
                    $table->index('sku', 'idx_products_sku');
                }
                if (Schema::hasColumn('products', 'is_active')) {
                    $table->index('is_active', 'idx_products_is_active');
                }
                if (Schema::hasColumn('products', 'created_at')) {
                    $table->index('created_at', 'idx_products_created_at');
                }
            });
        }

        // Order items (pivot) indexes
        if (Schema::hasTable('order_product')) {
            Schema::table('order_product', function (Blueprint $table) {
                if (Schema::hasColumn('order_product', 'order_id')) {
                    $table->index('order_id', 'idx_order_product_order_id');
                }
                if (Schema::hasColumn('order_product', 'product_id')) {
                    $table->index('product_id', 'idx_order_product_product_id');
                }
                if (Schema::hasColumn('order_product', 'variant_id')) {
                    $table->index('variant_id', 'idx_order_product_variant_id');
                }
            });
        }

        // Cart items indexes
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                if (Schema::hasColumn('cart_items', 'user_id')) {
                    $table->index('user_id', 'idx_cart_items_user_id');
                }
                if (Schema::hasColumn('cart_items', 'product_id')) {
                    $table->index('product_id', 'idx_cart_items_product_id');
                }
                if (Schema::hasColumn('cart_items', 'variant_id')) {
                    $table->index('variant_id', 'idx_cart_items_variant_id');
                }
            });
        }
    }

    public function down(): void
    {
        // Drop indexes created above (ignore errors if missing)
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                foreach (['idx_orders_created_at','idx_orders_status','idx_orders_payment_status','idx_orders_external_id','idx_orders_invoice_no','idx_orders_resi','idx_orders_user_id'] as $idx) {
                    try { $table->dropIndex($idx); } catch (\Throwable $e) {}
                }
            });
        }
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach (['idx_users_name','idx_users_created_at'] as $idx) {
                    try { $table->dropIndex($idx); } catch (\Throwable $e) {}
                }
            });
        }
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                foreach (['idx_products_name','idx_products_sku','idx_products_is_active','idx_products_created_at'] as $idx) {
                    try { $table->dropIndex($idx); } catch (\Throwable $e) {}
                }
            });
        }
        if (Schema::hasTable('order_product')) {
            Schema::table('order_product', function (Blueprint $table) {
                foreach (['idx_order_product_order_id','idx_order_product_product_id','idx_order_product_variant_id'] as $idx) {
                    try { $table->dropIndex($idx); } catch (\Throwable $e) {}
                }
            });
        }
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                foreach (['idx_cart_items_user_id','idx_cart_items_product_id','idx_cart_items_variant_id'] as $idx) {
                    try { $table->dropIndex($idx); } catch (\Throwable $e) {}
                }
            });
        }
    }
};
