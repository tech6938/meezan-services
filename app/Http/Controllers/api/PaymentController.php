<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\JazzCashService;
use App\Models\CommissionLog;
use App\Models\Provider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected JazzCashService $jazzCash;

    public function __construct(JazzCashService $jazzCash)
    {
        $this->jazzCash = $jazzCash;
    }

    // public function pay(Request $request)
    // {
    //     $request->validate([
    //         'mobile_number' => 'required|string',
    //         'cnic_last_6'   => 'nullable|string|size:6',
    //         'amount'        => 'required|numeric|min:1',
    //     ]);

    //     $txnRefNo = 'T' . date('YmdHis');

    //     $response = $this->jazzCash->payMWallet(
    //         $request->mobile_number,
    //         $request->cnic_last_6 ?? '',
    //         $request->amount,
    //         $txnRefNo
    //     );

    //     // Verify response signature integrity
    //     if (!$this->jazzCash->validateResponseHash($response)) {
    //         return response()->json([
    //             'status'  => 'failed',
    //             'message' => 'Response signature verification failed.',
    //         ], 400);
    //     }

    //     if (isset($response['pp_ResponseCode']) && $response['pp_ResponseCode'] === '000') {
    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'OTP sent to mobile device. Confirm transaction on phone.',
    //             'data'    => $response
    //         ]);
    //     }

    //     return response()->json([
    //         'status'  => 'failed',
    //         'message' => $response['pp_ResponseMessage'] ?? 'Transaction failed.',
    //         'data'    => $response
    //     ], 400);
    // }

    // public function pay(Request $request)
    // {
    //     // Validate request
    //     $validator = Validator::make($request->all(), [
    //         'mobile_number' => 'required|string|regex:/^03[0-9]{9}$/',
    //         'cnic_last_6'   => 'nullable|string|size:6',
    //         'amount'        => 'required|numeric|min:1',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => 'failed',
    //             'message' => $validator->errors()->first(),
    //         ], 422);
    //     }

    //     // Get authenticated provider
    //     $provider = $request->user('provider-api');
    //     if (!$provider) {
    //         return response()->json([
    //             'status'  => 'failed',
    //             'message' => 'Provider not authenticated',
    //         ], 401);
    //     }

    //     // Generate unique transaction reference
    //     $txnRefNo = 'T' . date('YmdHis') . rand(100, 999);

    //     // ========== CREATE DATABASE RECORD (from initiateDeposit) ==========
    //     try {
    //         $transaction = CommissionLog::create([
    //             'provider_id' => $provider->id,
    //             'transaction_type' => 'deposit',
    //             'gateway' => 'JazzCash',
    //             'commission_deducted' => 0,
    //             'old_balance' => $provider->wallet_balance ?? 0,
    //             'new_balance' => $provider->wallet_balance ?? 0,
    //             'status' => 'pending',
    //             'transaction_reference' => $txnRefNo,
    //             'booking_price' => $request->amount,
    //             'commission_type' => 'deposit',
    //             'commission_rate' => 0,
    //         ]);

    //         Log::info('JazzCash Transaction Record Created', [
    //             'transaction_id' => $transaction->id,
    //             'reference' => $txnRefNo,
    //             'provider_id' => $provider->id,
    //             'amount' => $request->amount,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to create transaction record: ' . $e->getMessage());
    //         return response()->json([
    //             'status'  => 'failed',
    //             'message' => 'Failed to initialize transaction. Please try again.',
    //         ], 500);
    //     }
    //     // ================================================================

    //     // ========== SEND PAYMENT REQUEST TO JAZZCASH ==========
    //     try {
    //         $response = $this->jazzCash->payMWallet(
    //             $request->mobile_number,
    //             $request->cnic_last_6 ?? '',
    //             $request->amount,
    //             $txnRefNo
    //         );
    //     } catch (\Exception $e) {
    //         // Update transaction as failed
    //         $transaction->update([
    //             'status' => 'failed',
    //             'response_data' => ['error' => $e->getMessage()],
    //         ]);

    //         Log::error('JazzCash Payment Request Failed: ' . $e->getMessage());
    //         return response()->json([
    //             'status'  => 'failed',
    //             'message' => 'Payment request failed: ' . $e->getMessage(),
    //         ], 500);
    //     }
    //     // ======================================================

    //     // Verify response signature integrity
    //     if (!$this->jazzCash->validateResponseHash($response)) {
    //         // Update transaction as failed
    //         $transaction->update([
    //             'status' => 'failed',
    //             'response_data' => $response,
    //         ]);

    //         return response()->json([
    //             'status'  => 'failed',
    //             'message' => 'Response signature verification failed.',
    //         ], 400);
    //     }

    //     // ========== PROCESS RESPONSE ==========
    //     if (isset($response['pp_ResponseCode']) && $response['pp_ResponseCode'] === '000') {
    //         // Update transaction with response data
    //         $transaction->update([
    //             'status' => 'processing',
    //             'response_data' => $response,
    //         ]);

    //         Log::info('JazzCash Payment Initiated Successfully', [
    //             'transaction_id' => $transaction->id,
    //             'reference' => $txnRefNo,
    //             'response' => $response,
    //         ]);

    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'OTP sent to mobile device. Confirm transaction on phone.',
    //             'data'    => [
    //                 'transaction_id' => $transaction->id,
    //                 'transaction_reference' => $txnRefNo,
    //                 'amount' => $request->amount,
    //                 'response' => $response,
    //                 'success_url' => 'meezan_services://payment/success?ref=' . $txnRefNo,
    //                 'failure_url' => 'meezan_services://payment/failure?ref=' . $txnRefNo,
    //             ]
    //         ]);
    //     }

    //     // Handle failed/cancelled response
    //     $transaction->update([
    //         'status' => 'failed',
    //         'response_data' => $response,
    //     ]);

    //     Log::warning('JazzCash Payment Failed', [
    //         'transaction_id' => $transaction->id,
    //         'reference' => $txnRefNo,
    //         'response_code' => $response['pp_ResponseCode'] ?? 'N/A',
    //         'response_message' => $response['pp_ResponseMessage'] ?? 'Unknown error',
    //     ]);

    //     return response()->json([
    //         'status'  => 'failed',
    //         'message' => $response['pp_ResponseMessage'] ?? 'Transaction failed.',
    //         'data'    => [
    //             'transaction_id' => $transaction->id,
    //             'transaction_reference' => $txnRefNo,
    //             'response' => $response,
    //         ]
    //     ], 400);
    //     // =================================
    // }

    public function pay(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string|regex:/^03[0-9]{9}$/',
            'cnic_last_6'   => 'nullable|string|size:6',
            'amount'        => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Get authenticated provider
        $provider = $request->user('provider-api');
        if (!$provider) {
            return response()->json([
                'status'  => false,
                'message' => 'Provider not authenticated',
            ], 401);
        }

        // Generate unique transaction reference
        $txnRefNo = 'T' . date('YmdHis') . rand(100, 999);

        // ========== CREATE DATABASE RECORD ==========
        try {
            $transaction = CommissionLog::create([
                'provider_id' => $provider->id,
                'shopkeeper_id' => null,
                'transaction_type' => 'deposit',
                'gateway' => 'JazzCash',
                'commission_deducted' => 0,
                'old_balance' => $provider->wallet_balance ?? 0,
                'new_balance' => $provider->wallet_balance ?? 0,
                'status' => 'pending',
                'transaction_reference' => $txnRefNo,
                'booking_price' => $request->amount,
                'commission_type' => 'deposit',
                'commission_rate' => 0,
            ]);

            Log::info('JazzCash Transaction Record Created', [
                'transaction_id' => $transaction->id,
                'reference' => $txnRefNo,
                'provider_id' => $provider->id,
                'amount' => $request->amount,
            ]);
        } catch (\Exception $e) {
            // Log::error('Failed to create transaction record: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to initialize transaction. Please try again.',
            ], 500);
        }
        // ==============================================

        // ========== SEND PAYMENT REQUEST TO JAZZCASH ==========
        try {
            $response = $this->jazzCash->payMWallet(
                $request->mobile_number,
                $request->cnic_last_6 ?? '',
                $request->amount,
                $txnRefNo
            );
        } catch (\Exception $e) {
            // Update transaction as failed
            $transaction->update([
                'status' => 'failed',
                'response_data' => ['error' => $e->getMessage()],
            ]);

            // Log::error('JazzCash Payment Request Failed: ' . $e->getMessage());
            // return response()->json([
            //     'status'  => false,
            //     'message' => 'Payment request failed: ' . $e->getMessage(),
            // ], 500);
        }
        // ======================================================

        // ========== VERIFY RESPONSE ==========
        // Verify response signature integrity
        if (!$this->jazzCash->validateResponseHash($response)) {
            $transaction->update([
                'status' => 'failed',
                'response_data' => $response,
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Response signature verification failed.',
                // Commented JazzCash response for debugging
                // 'jazzcash_response' => $response,
            ], 400);
        }

        // ========== PROCESS RESPONSE ==========
        if (isset($response['pp_ResponseCode']) && $response['pp_ResponseCode'] === '000') {
            // Update transaction
            $transaction->update([
                'status' => 'processing',
                'response_data' => $response,
                'transaction_id' => $response['pp_TxnRefNo'] ?? $txnRefNo,
            ]);

            // Log::info('JazzCash Payment Initiated Successfully', [
            //     'transaction_id' => $transaction->id,
            //     'reference' => $txnRefNo,
            //     'response' => $response,
            // ]);

            // ========== CUSTOM RESPONSE FORMAT ==========
            return response()->json([
                'status' => true,
                'message' => 'OTP sent to mobile device. Please confirm transaction on your phone.',
                'data' => [
                    'provider_id' => $provider->id,
                    'shopkeeper_id' => null,
                    'transaction_id' => $transaction->id,
                    'transaction_reference' => $txnRefNo,
                    'gateway' => 'JazzCash',
                    'transaction_type' => 'credit',
                    'amount' => (string) $request->amount,
                    'pre_balance' => number_format($transaction->old_balance ?? 0, 2),
                    'post_balance' => number_format($transaction->new_balance ?? 0, 2),
                    'updated_at' => $transaction->updated_at->toISOString(),
                    'created_at' => $transaction->created_at->toISOString(),
                    'id' => $transaction->id,
                ],
                // Commented JazzCash response for debugging
                // 'jazzcash_response' => $response,
                // 'jazzcash_response_code' => $response['pp_ResponseCode'] ?? null,
                // 'jazzcash_response_message' => $response['pp_ResponseMessage'] ?? null,
            ]);
            // =============================================
        }

        // ========== HANDLE FAILED RESPONSE ==========
        $transaction->update([
            'status' => 'failed',
            'response_data' => $response,
        ]);

        // Log::warning('JazzCash Payment Failed', [
        //     'transaction_id' => $transaction->id,
        //     'reference' => $txnRefNo,
        //     'response_code' => $response['pp_ResponseCode'] ?? 'N/A',
        //     'response_message' => $response['pp_ResponseMessage'] ?? 'Unknown error',
        // ]);

        // ========== FAILURE RESPONSE ==========
        return response()->json([
            'status' => false,
            'message' => $response['pp_ResponseMessage'] ?? 'Transaction failed.',
            'data' => [
                'provider_id' => $provider->id,
                'transaction_id' => $transaction->id,
                'transaction_reference' => $txnRefNo,
                'gateway' => 'JazzCash',
                'transaction_type' => 'credit',
                'amount' => (string) $request->amount,
                'pre_balance' => number_format($transaction->old_balance ?? 0, 2),
                'post_balance' => number_format($transaction->new_balance ?? 0, 2),
                'updated_at' => $transaction->updated_at->toISOString(),
                'created_at' => $transaction->creataed_at->toISOString(),
                'id' => $transaction->id,
            ],
            // Commented JazzCash response for debugging
            // 'jazzcash_response' => $response,
            // 'jazzcash_response_code' => $response['pp_ResponseCode'] ?? null,
            // 'jazzcash_response_message' => $response['pp_ResponseMessage'] ?? null,
        ], 400);
        // =====================================
    }

    public function checkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'txn_ref_no' => 'required|string',
        ]);

        $response = $this->jazzCash->inquiryStatus($request->txn_ref_no);

        // Verify HMAC response hash signature
        if (!$this->jazzCash->validateResponseHash($response)) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Response signature verification failed.',
                'data'    => $response
            ], 400);
        }

        $apiResponseCode     = $response['pp_ResponseCode'] ?? '';
        $paymentResponseCode = $response['pp_PaymentResponseCode'] ?? '';
        $paymentStatus       = $response['pp_Status'] ?? '';

        // Validate API success (000) AND payment execution success (121 / Completed)
        if ($apiResponseCode === '000') {
            if ($paymentResponseCode === '121' || strtolower($paymentStatus) === 'completed') {
                return response()->json([
                    'status'         => 'success',
                    'payment_status' => 'Completed',
                    'message'        => $response['pp_PaymentResponseMessage'] ?? 'Transaction processed successfully.',
                    'retrieval_ref'  => $response['pp_RetrievalReferenceNo'] ?? null,
                    'raw_data'       => $response,
                ]);
            }

            return response()->json([
                'status'         => 'pending_or_failed',
                'payment_status' => $paymentStatus ?: 'Pending',
                'message'        => $response['pp_PaymentResponseMessage'] ?? 'Transaction is not completed yet.',
                'raw_data'       => $response,
            ], 400);
        }

        return response()->json([
            'status'  => 'failed',
            'message' => $response['pp_ResponseMessage'] ?? 'Status inquiry request failed.',
            'data'    => $response,
        ], 400);
    }

    /**
     * Process Transaction Refund
     */
    // public function refund(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'txn_ref_no' => 'required|string',
    //         'amount'     => 'required|numeric|min:1',
    //     ]);
    //     // dd($request->all());

    //     $response = $this->jazzCash->refundTransaction($request->txn_ref_no, $request->amount);

    //     // Validate response signature
    //     if (!$this->jazzCash->validateResponseHash($response)) {
    //         return response()->json([
    //             'status'  => 'failed',
    //             'message' => 'Response signature verification failed.',
    //             'data'    => $response
    //         ], 400);
    //     }

    //     $responseCode = $response['pp_ResponseCode'] ?? '';

    //     if ($responseCode === '000') {
    //         return response()->json([
    //             'status'              => 'success',
    //             'message'             => $response['pp_ResponseMessage'] ?? 'Refund processed successfully.',
    //             'refund_amount'       => $response['pp_RefundAmount'] ?? $request->amount,
    //             'total_refund_amount' => $response['pp_TotalRefundAmount'] ?? $request->amount,
    //             'raw_data'            => $response,
    //         ]);
    //     }

    //     return response()->json([
    //         'status'  => 'failed',
    //         'message' => $response['pp_ResponseMessage'] ?? 'Refund request failed.',
    //         'data'    => $response,
    //     ], 400);
    // }

    public function refund(Request $request)
    {
        $request->validate([
            'txn_ref_no'    => 'required|string',
            'amount'        => 'required|numeric',
            'merchant_mpin' => 'required|string'
        ]);

        $response = $this->jazzCash->refundTransaction(
            $request->txn_ref_no,
            $request->amount,
            $request->merchant_mpin
        );

        // Update transaction
        $transaction = CommissionLog::where('transaction_reference', $request->txn_ref_no)->first();
        if ($transaction) {
            $transaction->update([
                'response_data' => $response,
            ]);

            if (isset($response['pp_ResponseCode']) && $response['pp_ResponseCode'] === '000') {
                $transaction->update([
                    'status' => 'refunded',
                    'completed_at' => now(),
                ]);
            }
        }

        return response()->json($response);
    }

    /**
     * Merchant Account Balance Inquiry API
     */
    public function merchantBalance()
    {
        $response = $this->jazzCash->getMerchantBalance();

        // Verify response hash signature
        if (!$this->jazzCash->validateResponseHash($response)) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Response signature verification failed.',
                'data'    => $response
            ], 400);
        }

        if (isset($response['pp_ResponseCode']) && $response['pp_ResponseCode'] === '000') {
            // Convert paisas to PKR (JazzCash returns balance in paisas)
            $balanceInPaisa = (float) ($response['pp_Balance'] ?? 0);
            $balanceInPKR   = $balanceInPaisa / 100;

            return response()->json([
                'status'           => 'success',
                'message'          => $response['pp_ResponseMessage'] ?? 'Balance fetched successfully',
                'balance_pkr'      => $balanceInPKR,
                'raw_data'         => $response
            ]);
        }

        return response()->json([
            'status'  => 'failed',
            'message' => $response['pp_ResponseMessage'] ?? 'Unable to fetch balance.',
            'data'    => $response
        ], 400);
    }
}
