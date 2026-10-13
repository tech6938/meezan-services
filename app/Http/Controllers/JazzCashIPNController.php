<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\JazzCashDepositSettlementService;
use App\Services\JazzCashService;

class JazzCashIPNController extends Controller
{
    protected JazzCashService $jazzCashService;
    protected JazzCashDepositSettlementService $depositSettlement;

    public function __construct(JazzCashService $jazzCashService, JazzCashDepositSettlementService $depositSettlement)
    {
        $this->jazzCashService = $jazzCashService;
        $this->depositSettlement = $depositSettlement;
    }

    public function handleIPN(Request $request): JsonResponse
    {
        $data = $request->all();
        $reference = $data['pp_TxnRefNo'] ?? null;

        // Securely validate incoming IPN Hash
        if (!$this->jazzCashService->validateResponseHash($data)) {
            // Log::warning('JazzCash IPN signature validation failed.', [
            //     'reference' => $reference,
            // ]);

            return $this->signedResponse('999', 'Invalid Secure Hash', 400);
        }

        // Log::info('JazzCash IPN received.', [
        //     'reference' => $reference,
        //     'response_code' => $data['pp_ResponseCode'] ?? null,
        //     'payment_response_code' => $data['pp_PaymentResponseCode'] ?? null,
        //     'payment_status' => $data['pp_Status'] ?? null,
        // ]);

        if ($this->depositSettlement->isSuccessfulResponse($data)) {
            if (!$this->depositSettlement->settle($data)) {
                // Log::error('JazzCash completed deposit IPN could not credit provider wallet.', [
                //     'reference' => $reference,
                // ]);

                return $this->signedResponse('999', 'Deposit transaction could not be settled.', 500);
            }

            // Log::info('JazzCash deposit wallet credited from IPN.', [
            //     'reference' => $reference,
            // ]);
        }

        return $this->signedResponse('000', 'IPN received successfully');
    }

    private function signedResponse(string $responseCode, string $responseMessage, int $statusCode = 200): JsonResponse
    {
        $payload = [
            'pp_ResponseCode' => $responseCode,
            'pp_ResponseMessage' => $responseMessage,
        ];
        $payload['pp_SecureHash'] = $this->jazzCashService->generateHash($payload);

        return response()->json($payload, $statusCode);
    }
}
