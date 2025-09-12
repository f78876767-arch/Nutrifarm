<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('order_product_id')->constrained('order_product')->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->foreignId('variant_id')->nullable()->constrained()->nullOnDelete();
                $table->tinyInteger('rating'); // 1..5
                $table->text('comment')->nullable();
                $table->boolean('is_approved')->default(true);
                $table->timestamps();

                $table->unique('order_product_id');
                $table->index(['product_id']);
                $table->index(['user_id']);
                $table->index(['product_id','rating']);
            });
            return;
        }

        // If the table already exists (e.g., created by an earlier migration), add missing columns without FK constraints
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'user_id')) {
                $table->unsignedBigInteger('user_id');
            }
            if (!Schema::hasColumn('reviews', 'order_id')) {
                $table->unsignedBigInteger('order_id');
            }
            if (!Schema::hasColumn('reviews', 'order_product_id')) {
                $table->unsignedBigInteger('order_product_id');
            }
            if (!Schema::hasColumn('reviews', 'product_id')) {
                $table->unsignedBigInteger('product_id');
            }
            if (!Schema::hasColumn('reviews', 'variant_id')) {
                $table->unsignedBigInteger('variant_id')->nullable();
            }
            if (!Schema::hasColumn('reviews', 'rating')) {
                $table->tinyInteger('rating');
            }
            if (!Schema::hasColumn('reviews', 'comment')) {
                $table->text('comment')->nullable();
            }
            if (!Schema::hasColumn('reviews', 'is_approved')) {
                $table->boolean('is_approved')->default(true);
            }
            // timestamps columns typically exist from earlier create; add if missing
            if (!Schema::hasColumn('reviews', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('reviews', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
        // Add indexes if table exists and columns are present (Laravel will ignore duplicates on SQLite silently or throw; acceptable in dev)
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'order_product_id')) {
                try { $table->unique('order_product_id'); } catch (Throwable $e) { /* ignore */ }
            }
            if (Schema::hasColumn('reviews', 'product_id')) {
                try { $table->index(['product_id']); } catch (Throwable $e) { /* ignore */ }
            }
            if (Schema::hasColumn('reviews', 'user_id')) {
                try { $table->index(['user_id']); } catch (Throwable $e) { /* ignore */ }
            }
            if (Schema::hasColumn('reviews', 'product_id') && Schema::hasColumn('reviews', 'rating')) {
                try { $table->index(['product_id','rating']); } catch (Throwable $e) { /* ignore */ }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
