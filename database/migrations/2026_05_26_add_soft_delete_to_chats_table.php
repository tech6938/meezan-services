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
        Schema::table('chats', function (Blueprint $table) {
            // Add booking_id if it doesn't exist
            if (!Schema::hasColumn('chats', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('id');
                $table->foreign('booking_id')
                    ->references('id')
                    ->on('booking_requests')
                    ->onDelete('cascade');
            }

            // Add file-related columns if they don't exist
            if (!Schema::hasColumn('chats', 'file_name')) {
                $table->string('file_name')->nullable()->after('message');
                $table->string('file_type')->nullable()->after('file_name');
                $table->string('file_path')->nullable()->after('file_type');
            }

            // Add soft delete support
            if (!Schema::hasColumn('chats', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            if (Schema::hasColumn('chats', 'booking_id')) {
                $table->dropForeign(['booking_id']);
                $table->dropColumn('booking_id');
            }

            if (Schema::hasColumn('chats', 'file_name')) {
                $table->dropColumn(['file_name', 'file_type', 'file_path']);
            }

            if (Schema::hasColumn('chats', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};
