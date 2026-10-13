<?php

namespace Tests\Unit;

use App\Services\JazzCashService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class JazzCashServiceTest extends TestCase
{
    public function test_response_hash_is_required_for_error_responses(): void
    {
        $service = $this->serviceWithSalt('test-salt');

        $this->assertFalse($service->validateResponseHash([
            'pp_ResponseCode' => '123',
            'pp_ResponseMessage' => 'Rejected',
        ]));
    }

    public function test_response_hash_must_match_the_response_payload(): void
    {
        $service = $this->serviceWithSalt('test-salt');

        $this->assertFalse($service->validateResponseHash([
            'pp_ResponseCode' => '000',
            'pp_SecureHash' => 'invalid-hash',
        ]));
    }

    public function test_valid_response_hash_is_accepted(): void
    {
        $service = $this->serviceWithSalt('test-salt');
        $response = [
            'pp_ResponseCode' => '000',
            'pp_ResponseMessage' => 'Success',
        ];
        $response['pp_SecureHash'] = $service->generateHash($response);

        $this->assertTrue($service->validateResponseHash($response));
    }

    public function test_response_codes_are_classified_as_requested(): void
    {
        $service = $this->serviceWithSalt('test-salt');

        $this->assertSame('success', $service->classifyResponse(['pp_ResponseCode' => '000']));
        $this->assertSame('success', $service->classifyResponse(['pp_PaymentResponseCode' => '121']));
        $this->assertSame('pending', $service->classifyResponse(['pp_PaymentResponseCode' => '124']));
        $this->assertSame('pending', $service->classifyResponse(['pp_ResponseCode' => '157']));
        $this->assertSame('failure', $service->classifyResponse(['pp_ResponseCode' => '999']));
    }

    public function test_payment_response_pending_code_overrides_api_success_code(): void
    {
        $service = $this->serviceWithSalt('test-salt');

        $this->assertSame('pending', $service->classifyResponse([
            'pp_ResponseCode' => '000',
            'pp_PaymentResponseCode' => '157',
        ]));

        $this->assertSame('failure', $service->classifyResponse([
            'pp_ResponseCode' => '999',
            'pp_Status' => 'Completed',
        ]));

        $this->assertSame('pending', $service->classifyResponse([
            'pp_ResponseCode' => '000',
            'pp_PaymentResponseCode' => '124',
            'pp_Status' => 'Completed',
        ]));
    }

    private function serviceWithSalt(string $salt): JazzCashService
    {
        $reflection = new ReflectionClass(JazzCashService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('salt')->setValue($service, $salt);

        return $service;
    }
}
