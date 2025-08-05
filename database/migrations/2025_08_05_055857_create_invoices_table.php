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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('xendit_id')->unique(); // Xendit invoice id
            $table->string('external_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('status');
            $table->decimal('amount', 18, 2);
            $table->string('invoice_url');
            $table->string('payer_email')->nullable();
            $table->string('description')->nullable();
            $table->string('currency')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->json('raw')->nullable(); // Store full Xendit response for reference
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
