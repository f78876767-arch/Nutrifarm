<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('variants')) {
            // Table not created yet; skip and let later migrations handle
            return;
        }
        if (!Schema::hasColumn('variants', 'unit')) {
            Schema::table('variants', function (Blueprint $table) {
                $table->string('unit', 32)->nullable()->after('value');
            });
        }
        if (!Schema::hasColumn('variants', 'custom_unit')) {
            Schema::table('variants', function (Blueprint $table) {
                $table->string('custom_unit', 64)->nullable()->after('unit');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('variants')) {
            return;
        }
        Schema::table('variants', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('variants', 'custom_unit')) {
                $drops[] = 'custom_unit';
            }
            if (Schema::hasColumn('variants', 'unit')) {
                $drops[] = 'unit';
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
