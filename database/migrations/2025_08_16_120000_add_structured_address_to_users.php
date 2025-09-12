<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('province_id')->nullable()->after('address');
            $table->string('province_name')->nullable()->after('province_id');
            $table->unsignedBigInteger('city_id')->nullable()->after('province_name');
            $table->string('city_name')->nullable()->after('city_id');
            $table->string('postal_code', 10)->nullable()->after('city_name');
            $table->unsignedBigInteger('subdistrict_id')->nullable()->after('postal_code'); // pro only
            $table->string('subdistrict_name')->nullable()->after('subdistrict_id');
            $table->string('phone')->nullable()->after('subdistrict_name');
            $table->index(['city_id']);
            $table->index(['province_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['city_id']);
            $table->dropIndex(['province_id']);
            $table->dropColumn([
                'province_id','province_name','city_id','city_name','postal_code','subdistrict_id','subdistrict_name','phone'
            ]);
        });
    }
};
