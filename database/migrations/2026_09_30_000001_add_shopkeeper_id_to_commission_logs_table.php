<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('commission_logs', 'shopkeeper_id')) {
            Schema::table('commission_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('shopkeeper_id')->nullable()->after('provider_id');
                $table->index('shopkeeper_id');
            });
        }
    }

    public function down(): void
    {
    }
};