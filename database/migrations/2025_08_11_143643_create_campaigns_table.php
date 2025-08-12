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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['email', 'banner', 'discount', 'flash_sale', 'newsletter', 'social_media', 'push_notification']);
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->decimal('budget', 15, 2)->default(0);
            $table->decimal('spent_budget', 15, 2)->default(0);
            $table->json('target_audience')->nullable();
            $table->json('campaign_data')->nullable();
            $table->json('metrics')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['type', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
