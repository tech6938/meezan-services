<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 32)->nullable()->unique()->after('device_id');
            }

            if (!Schema::hasColumn('users', 'referred_by_user_id')) {
                $table->foreignId('referred_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('referral_code');
            }

            if (!Schema::hasColumn('users', 'referral_total_referrals')) {
                $table->unsignedInteger('referral_total_referrals')->default(0)->after('referred_by_user_id');
            }

            if (!Schema::hasColumn('users', 'referral_total_earned')) {
                $table->decimal('referral_total_earned', 12, 2)->default(0)->after('referral_total_referrals');
            }

            if (!Schema::hasColumn('users', 'referral_balance')) {
                $table->decimal('referral_balance', 12, 2)->default(0)->after('referral_total_earned');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'referral_balance')) {
                $table->dropColumn('referral_balance');
            }
            if (Schema::hasColumn('users', 'referral_total_earned')) {
                $table->dropColumn('referral_total_earned');
            }
            if (Schema::hasColumn('users', 'referral_total_referrals')) {
                $table->dropColumn('referral_total_referrals');
            }
            if (Schema::hasColumn('users', 'referred_by_user_id')) {
                $table->dropConstrainedForeignId('referred_by_user_id');
            }
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropUnique(['referral_code']);
                $table->dropColumn('referral_code');
            }
        });
    }
};
