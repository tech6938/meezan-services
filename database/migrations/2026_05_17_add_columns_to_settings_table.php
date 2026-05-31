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
        Schema::table('settings', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('settings', 'website_url')) {
                $table->string('website_url')->nullable()->after('app_url');
            }
            if (!Schema::hasColumn('settings', 'twitter_url')) {
                $table->string('twitter_url')->nullable()->after('website_url');
            }
            if (!Schema::hasColumn('settings', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('twitter_url');
            }
            if (!Schema::hasColumn('settings', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('instagram_url');
            }
            if (!Schema::hasColumn('settings', 'facebook_url')) {
                $table->string('facebook_url')->nullable()->after('youtube_url');
            }
            if (!Schema::hasColumn('settings', 'terms_and_conditions_url')) {
                $table->string('terms_and_conditions_url')->nullable()->after('facebook_url');
            }
            if (!Schema::hasColumn('settings', 'customer_video_tutorial_url')) {
                $table->string('customer_video_tutorial_url')->nullable()->after('terms_and_conditions_url');
            }
            if (!Schema::hasColumn('settings', 'provider_video_tutorial_url')) {
                $table->string('provider_video_tutorial_url')->nullable()->after('customer_video_tutorial_url');
            }
            if (!Schema::hasColumn('settings', 'appIsOn')) {
                $table->boolean('appIsOn')->default(true)->after('provider_video_tutorial_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'website_url',
                'twitter_url',
                'instagram_url',
                'youtube_url',
                'facebook_url',
                'terms_and_conditions_url',
                'customer_video_tutorial_url',
                'provider_video_tutorial_url',
                'appIsOn'
            ]);
        });
    }
};
