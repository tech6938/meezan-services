<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\JazzCashService;

class JazzCashIPNController extends Controller
{
    protected JazzCashService $jazzCashService;

    public function __construct(JazzCashService $jazzCashService)
    {
        $this->jazzCashService = $jazzCashService;
    }

    public function handleIPN(Request $request): JsonResponse
    {
        $data = $request->all();

        // Securely validate incoming IPN Hash
        if (!$this->jazzCashService->validateResponseHash($data)) {
            return response()->json([
                'pp_ResponseCode'    => '999',
                'pp_ResponseMessage' => 'Invalid Secure Hash',
                'pp_SecureHash'      => '',
            ], 400);
        }

        // Process successful payment notification (Code 121 or 000)
        if (($data['pp_ResponseCode'] ?? '') === '121' || ($data['pp_ResponseCode'] ?? '') === '000') {
            // Update order status in database using $data['pp_TxnRefNo']
            // Order::where('txn_ref_no', $data['pp_TxnRefNo'])->update(['status' => 'paid']);
        }

        return response()->json([
            'pp_ResponseCode'    => '000',
            'pp_ResponseMessage' => 'IPN received successfully',
            'pp_SecureHash'      => '',
        ]);
    }
}
