<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Services\CommissionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CommissionServiceLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subcat_id')->nullable();
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->string('type');
            $table->decimal('amount', 10, 2);
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('shopkeeper_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

        Schema::create('commission_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->string('commission_type');
            $table->decimal('commission_rate', 10, 2);
            $table->decimal('booking_price', 10, 2);
            $table->decimal('commission_deducted', 10, 2);
            $table->decimal('old_balance', 10, 2);
            $table->decimal('new_balance', 10, 2);
            $table->timestamps();
        });

        $migration = require database_path('migrations/2026_09_30_000001_add_shopkeeper_id_to_commission_logs_table.php');
        $migration->up();
    }

    protected function tearDown(): void
    {
        foreach (['commission_logs', 'wallets', 'commissions', 'service_requests'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_booking_commission_deduction_creates_a_commission_log(): void
    {
        DB::table('service_requests')->insert(['id' => 7, 'subcat_id' => 3]);
        DB::table('commissions')->insert([
            'sub_category_id' => 3,
            'type' => 'fixed',
            'amount' => 15,
        ]);
        DB::table('wallets')->insert([
            'provider_id' => 51,
            'amount' => 250,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $booking = new BookingRequest();
        $booking->id = 80;
        $booking->request_id = 7;
        $booking->provider_id = 51;
        $booking->shopkeeper_id = null;
        $booking->price = 100;

        $result = app(CommissionService::class)->processCommissionDeduction($booking);

        $this->assertTrue($result['success']);
        $this->assertSame(235.0, (float) DB::table('wallets')->where('provider_id', 51)->value('amount'));
        $this->assertDatabaseHas('commission_logs', [
            'booking_id' => 80,
            'provider_id' => 51,
            'shopkeeper_id' => null,
            'commission_deducted' => 15,
        ]);
    }
}