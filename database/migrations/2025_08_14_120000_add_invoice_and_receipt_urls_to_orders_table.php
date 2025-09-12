<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'invoice_pdf_url')) {
                $table->string('invoice_pdf_url')->nullable()->after('xendit_invoice_url');
            }
            if (!Schema::hasColumn('orders', 'receipt_pdf_url')) {
                $table->string('receipt_pdf_url')->nullable()->after('invoice_pdf_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'invoice_pdf_url')) {
                $table->dropColumn('invoice_pdf_url');
            }
            if (Schema::hasColumn('orders', 'receipt_pdf_url')) {
                $table->dropColumn('receipt_pdf_url');
            }
        });
    }
};
