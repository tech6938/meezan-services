<?php

namespace Tests\Unit;

use App\Services\JazzCashDepositSettlementService;
use PHPUnit\Framework\TestCase;

class JazzCashDepositSettlementServiceTest extends TestCase
{
    public function test_only_completed_payment_responses_are_successful(): void
    {
        $service = new JazzCashDepositSettlementService();

        $this->assertTrue($service->isSuccessfulResponse(['pp_ResponseCode' => '121']));
        $this->assertTrue($service->isSuccessfulResponse(['pp_PaymentResponseCode' => '121']));
        $this->assertTrue($service->isSuccessfulResponse(['pp_Status' => 'Completed']));
        $this->assertFalse($service->isSuccessfulResponse(['pp_ResponseCode' => '000']));
        $this->assertFalse($service->isSuccessfulResponse(['pp_Status' => 'Pending']));
    }
}
