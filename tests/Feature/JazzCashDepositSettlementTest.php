<?php

namespace Tests\Feature;

use App\Models\CommissionLog;
use App\Services\JazzCashDepositSettlementService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JazzCashDepositSettlementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::dropIfExists('commission_logs');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('providers');

        Schema::create('providers', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('commission_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_reference')->nullable();
            $table->string('transaction_type');
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->decimal('booking_price', 12, 2)->default(0);
            $table->decimal('old_balance', 12, 2)->default(0);
            $table->decimal('new_balance', 12, 2)->default(0);
            $table->string('status');
            $table->json('response_data')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('commission_logs');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('providers');

        parent::tearDown();
    }

    public function test_configured_ipn_route_credits_a_completed_deposit_once(): void
    {
        $this->assertTrue(Route::has('jazzcash.ipn.legacy'));
        $this->assertSame('api/jazzcash/ipn', Route::getRoutes()->getByName('jazzcash.ipn.legacy')->uri());

        DB::table('providers')->insert(['id' => 42]);
        DB::table('wallets')->insert([
            'provider_id' => 42,
            'amount' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        CommissionLog::create([
            'transaction_reference' => 'T-DEPOSIT-42',
            'transaction_type' => 'deposit',
            'provider_id' => 42,
            'booking_price' => 100,
            'old_balance' => 25,
            'new_balance' => 125,
            'status' => 'pending',
        ]);

        $service = app(JazzCashDepositSettlementService::class);
        $completedResponse = [
            'pp_TxnRefNo' => 'T-DEPOSIT-42',
            'pp_PaymentResponseCode' => '121',
        ];

        $this->assertTrue($service->settle($completedResponse));
        $this->assertTrue($service->settle($completedResponse));
        $this->assertSame(125.0, (float) DB::table('wallets')->where('provider_id', 42)->value('amount'));
        $this->assertSame('success', CommissionLog::where('transaction_reference', 'T-DEPOSIT-42')->value('status'));
    }
}