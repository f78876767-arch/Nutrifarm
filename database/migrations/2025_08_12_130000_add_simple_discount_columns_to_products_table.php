<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'discount_starts_at')) {
                $table->dateTime('discount_starts_at')->nullable()->after('discount_amount');
            }
            if (!Schema::hasColumn('products', 'discount_ends_at')) {
                $table->dateTime('discount_ends_at')->nullable()->after('discount_starts_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'discount_ends_at')) {
                $table->dropColumn('discount_ends_at');
            }
            if (Schema::hasColumn('products', 'discount_starts_at')) {
                $table->dropColumn('discount_starts_at');
            }
            if (Schema::hasColumn('products', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};
