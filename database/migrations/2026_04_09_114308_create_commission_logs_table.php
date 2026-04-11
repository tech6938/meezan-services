<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('commission_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('shopkeeper_id')->nullable();
            $table->unsignedBigInteger('sub_category_id');
            $table->string('commission_type');
            $table->decimal('commission_rate', 10, 2);
            $table->decimal('booking_price', 10, 2);
            $table->decimal('commission_deducted', 10, 2);
            $table->decimal('old_balance', 10, 2);
            $table->decimal('new_balance', 10, 2);
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('booking_requests')->onDelete('cascade');
            $table->index('provider_id');
            $table->index('shopkeeper_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('commission_logs');
    }
};
