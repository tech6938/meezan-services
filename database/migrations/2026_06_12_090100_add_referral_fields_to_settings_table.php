<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'referral_enabled')) {
                $table->boolean('referral_enabled')->default(false)->after('providerAppIsOn');
            }

            if (!Schema::hasColumn('settings', 'referral_type')) {
                $table->string('referral_type', 20)->default('percentage')->after('referral_enabled');
            }

            if (!Schema::hasColumn('settings', 'referral_level_1')) {
                $table->decimal('referral_level_1', 12, 2)->default(0)->after('referral_type');
            }

            if (!Schema::hasColumn('settings', 'referral_level_2')) {
                $table->decimal('referral_level_2', 12, 2)->default(0)->after('referral_level_1');
            }

            if (!Schema::hasColumn('settings', 'referral_level_3')) {
                $table->decimal('referral_level_3', 12, 2)->default(0)->after('referral_level_2');
            }

            if (!Schema::hasColumn('settings', 'referral_min_amount')) {
                $table->decimal('referral_min_amount', 12, 2)->nullable()->after('referral_level_3');
            }

            if (!Schema::hasColumn('settings', 'referral_max_amount')) {
                $table->decimal('referral_max_amount', 12, 2)->nullable()->after('referral_min_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'referral_max_amount')) {
                $table->dropColumn('referral_max_amount');
            }
            if (Schema::hasColumn('settings', 'referral_min_amount')) {
                $table->dropColumn('referral_min_amount');
            }
            if (Schema::hasColumn('settings', 'referral_level_3')) {
                $table->dropColumn('referral_level_3');
            }
            if (Schema::hasColumn('settings', 'referral_level_2')) {
                $table->dropColumn('referral_level_2');
            }
            if (Schema::hasColumn('settings', 'referral_level_1')) {
                $table->dropColumn('referral_level_1');
            }
            if (Schema::hasColumn('settings', 'referral_type')) {
                $table->dropColumn('referral_type');
            }
            if (Schema::hasColumn('settings', 'referral_enabled')) {
                $table->dropColumn('referral_enabled');
            }
        });
    }
};
