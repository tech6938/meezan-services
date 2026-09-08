<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('commission_logs', function (Blueprint $table) {
            $table->string('transaction_reference')->nullable()->after('id');
            $table->enum('transaction_type', ['commission', 'deposit', 'withdrawal', 'refund'])->default('commission')->after('commission_type');
            $table->string('gateway')->default('Commission')->after('transaction_type');
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending')->after('new_balance');
            $table->json('response_data')->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('response_data');
        });
    }

    public function down()
    {
        Schema::table('commission_logs', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_reference',
                'transaction_type',
                'gateway',
                'status',
                'response_data',
                'completed_at'
            ]);
        });
    }
};