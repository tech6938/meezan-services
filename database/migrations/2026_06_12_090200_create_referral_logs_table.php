<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('booking_requests')->nullOnDelete();
            $table->foreignId('referrer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('level');
            $table->string('referral_code', 32)->nullable();
            $table->string('commission_type', 20)->default('percentage');
            $table->decimal('commission_rate', 12, 2)->default(0);
            $table->decimal('booking_amount', 12, 2)->default(0);
            $table->decimal('earned_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('credited');
            $table->timestamps();

            $table->unique(['booking_id', 'referrer_user_id', 'level'], 'referral_logs_booking_referrer_level_unique');
            $table->index(['referrer_user_id', 'created_at'], 'referral_logs_referrer_created_index');
            $table->index(['referred_user_id', 'created_at'], 'referral_logs_referred_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_logs');
    }
};
