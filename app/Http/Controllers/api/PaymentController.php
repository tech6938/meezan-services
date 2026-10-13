<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\JazzCashDepositSettlementService;
use App\Services\JazzCashService;
use App\Models\CommissionLog;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected JazzCashService $jazzCash;
    protected JazzCashDepositSettlementService $depositSettlement;

    public function __construct(JazzCashService $jazzCash, JazzCashDepositSettlementService $depositSettlement)
    {
        $this->jazzCash = $jazzCash;
        $this->depositSettlement = $depositSettlement;
    }

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
        $amount = (float) $request->input('amount');
        $currentBalance = (float) ($provider->wallet_balance ?? 0);

        // ========== CREATE DATABASE RECORD ==========
        try {
            $transaction = CommissionLog::create([
                'provider_id' => $provider->id,
                // 'shopkeeper_id' => null,
                'transaction_type' => 'deposit',
                'gateway' => 'JazzCash',
                'commission_deducted' => 0,
                'old_balance' => $currentBalance,
                'new_balance' => $currentBalance + $amount,
                'status' => 'pending',  // ✅ Use 'pending' instead of 'processing'
                'transaction_reference' => $txnRefNo,
                'booking_price' => $amount,
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
            Log::error('Failed to create transaction record: ' . $e->getMessage());
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

            Log::error('JazzCash Payment Request Failed: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Payment request failed: ' . $e->getMessage(),
            ], 500);
        }
        // ======================================================

        // ========== VERIFY RESPONSE ==========
        if (!$this->jazzCash->validateResponseHash($response)) {
            $transaction->update([
                'status' => 'failed',
                'response_data' => $response,
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Response signature verification failed.',
            ], 400);
        }

        // ========== PROCESS RESPONSE ==========
        $responseClassification = $this->jazzCash->classifyResponse($response);

        if ($responseClassification !== 'failure') {
            // ✅ Update with 'pending' status (not 'processing')
            $transaction->update([
                'status' => 'pending',  // ✅ Fixed: Use 'pending'
                'response_data' => $response,
                'transaction_id' => $response['pp_TxnRefNo'] ?? $txnRefNo,
            ]);

            $paymentCompleted = $this->depositSettlement->isSuccessfulResponse($response);
            if ($paymentCompleted && !$this->depositSettlement->settle($response, $txnRefNo)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment was completed, but the provider wallet could not be credited.',
                ], 500);
            }

            Log::info('JazzCash Payment Initiated Successfully', [
                'transaction_id' => $transaction->id,
                'reference' => $txnRefNo,
                'response' => $response,
            ]);

            // ========== CUSTOM RESPONSE FORMAT ==========
            return response()->json([
                'status' => true,
                'message' => $paymentCompleted
                    ? 'Payment completed successfully.'
                    : ($responseClassification === 'pending'
                        ? ($response['pp_ResponseMessage'] ?? 'Transaction is pending.')
                        : 'OTP sent to mobile device. Please confirm transaction on your phone.'),
                'data' => [
                    'provider_id' => $provider->id,
                    // 'shopkeeper_id' => null,
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

        Log::warning('JazzCash Payment Failed', [
            'transaction_id' => $transaction->id,
            'reference' => $txnRefNo,
            'response_code' => $response['pp_ResponseCode'] ?? 'N/A',
            'response_message' => $response['pp_ResponseMessage'] ?? 'Unknown error',
        ]);

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
                'created_at' => $transaction->created_at->toISOString(),
                'id' => $transaction->id,
            ],
            // Commented JazzCash response for debugging
            // 'jazzcash_response' => $response,
            // 'jazzcash_response_code' => $response['pp_ResponseCode'] ?? null,
            // 'jazzcash_response_message' => $response['pp_ResponseMessage'] ?? null,
        ], 400);
        // =====================================
    }

    public function withdraw(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'mobile_number' => 'required|string|regex:/^03[0-9]{9}$/',
            'cnic_last_6' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $actor = Auth::guard('api')->user();

        if (!$actor) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $amount = (float) $request->input('amount');
        $txnRefNo = 'W' . now('Asia/Karachi')->format('YmdHis') . random_int(100, 999);

        try {
            [$wallet, $transaction] = DB::transaction(function () use ($actor, $amount, $txnRefNo) {
                $wallet = Wallet::where('user_id', $actor->id)
                    ->lockForUpdate()
                    ->first();

                if (!$wallet) {
                    $wallet = Wallet::create([
                        'user_id' => $actor->id,
                        'amount' => 0,
                    ]);
                }

                if ((float) $wallet->amount < $amount) {
                    throw new \DomainException('Insufficient wallet balance');
                }

                $oldBalance = (float) $wallet->amount;
                $wallet->amount = $oldBalance - $amount;
                $wallet->save();

                $transaction = CommissionLog::create([
                    'user_id' => $actor->id,
                    'transaction_reference' => $txnRefNo,
                    'transaction_type' => 'withdrawal',
                    'gateway' => 'JazzCash',
                    'commission_type' => 'withdrawal',
                    'commission_rate' => 0,
                    'booking_price' => $amount,
                    'commission_deducted' => $amount,
                    'old_balance' => $oldBalance,
                    'new_balance' => $wallet->amount,
                    'status' => 'pending',
                ]);

                return [$wallet, $transaction];
            });
        } catch (\DomainException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to reserve JazzCash withdrawal', [
                'reference' => $txnRefNo,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unable to initialize withdrawal.',
            ], 500);
        }

        try {
            $response = $this->jazzCash->payMWallet(
                $request->mobile_number,
                $request->cnic_last_6,
                $amount,
                $txnRefNo,
                'WD' . $transaction->id
            );
        } catch (\Throwable $e) {
            $response = ['error' => $e->getMessage()];
        }

        $responseClassification = $this->jazzCash->classifyResponse($response);
        $hashValid = $this->jazzCash->validateResponseHash($response);

        if ($responseClassification === 'failure' || !$hashValid) {
            DB::transaction(function () use ($transaction, $wallet, $response) {
                $lockedWallet = Wallet::whereKey($wallet->id)->lockForUpdate()->first();
                $lockedTransaction = CommissionLog::whereKey($transaction->id)->lockForUpdate()->first();

                if ($lockedTransaction?->status === 'pending') {
                    $lockedWallet->amount += (float) $transaction->booking_price;
                    $lockedWallet->save();
                    $lockedTransaction->update([
                        'status' => 'failed',
                        'new_balance' => $lockedWallet->amount,
                        'response_data' => $response,
                    ]);
                }
            });

            return response()->json([
                'status' => false,
                'message' => $hashValid
                    ? ($response['pp_ResponseMessage'] ?? 'Withdrawal request failed.')
                    : 'Response signature verification failed.',
                'data' => [
                    'transaction_reference' => $txnRefNo,
                    'transaction_id' => $transaction->id,
                ],
            ], 400);
        }

        $withdrawalCompleted = $this->depositSettlement->isSuccessfulResponse($response);
        if ($withdrawalCompleted) {
            $this->settleWithdrawal($txnRefNo, true, $response);
        }

        $transaction->update([
            'response_data' => $response,
        ]);

        return response()->json([
            'status' => true,
            'message' => $withdrawalCompleted
                ? 'Withdrawal completed successfully.'
                : ($responseClassification === 'pending'
                    ? ($response['pp_ResponseMessage'] ?? 'Withdrawal is pending.')
                    : 'Withdrawal request initiated. Please confirm the OTP on your phone.'),
            'data' => [
                'transaction_id' => $transaction->id,
                'transaction_reference' => $txnRefNo,
                'amount' => (string) $amount,
                'status' => 'pending',
                'old_balance' => number_format((float) $transaction->old_balance, 2),
                'new_balance' => number_format((float) $transaction->new_balance, 2),
            ],
        ]);
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

        $responseClassification = $this->jazzCash->classifyResponse($response);
        $paymentCompleted = $this->depositSettlement->isSuccessfulResponse($response);
        $paymentStatus       = $response['pp_Status'] ?? '';

        if ($responseClassification === 'success' && $paymentCompleted) {
            $deposit = CommissionLog::where('transaction_reference', $request->txn_ref_no)
                ->where('transaction_type', 'deposit')
                ->exists();

            if ($deposit) {
                if (!$this->depositSettlement->settle($response, $request->txn_ref_no)) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Payment was completed, but the provider wallet could not be credited.',
                    ], 500);
                }
            } else {
                $this->settleWithdrawal($request->txn_ref_no, true, $response);
            }

            return response()->json([
                'status'         => 'success',
                'payment_status' => 'Completed',
                'message'        => $response['pp_PaymentResponseMessage'] ?? 'Transaction processed successfully.',
                'retrieval_ref'  => $response['pp_RetrievalReferenceNo'] ?? null,
                'raw_data'       => $response,
            ]);
        }

        if ($responseClassification === 'pending'
            || ($responseClassification === 'success' && !$paymentCompleted)) {
            return response()->json([
                'status'         => 'pending',
                'payment_status' => $paymentStatus ?: 'Pending',
                'message'        => $response['pp_PaymentResponseMessage'] ?? 'Transaction is pending.',
                'raw_data'       => $response,
            ]);
        }

        if (in_array(strtolower((string) $paymentStatus), ['failed', 'cancelled'], true)) {
            $this->settleWithdrawal($request->txn_ref_no, false, $response);
        }

        return response()->json([
            'status'  => 'failed',
            'message' => $response['pp_ResponseMessage'] ?? 'Status inquiry request failed.',
            'data'    => $response,
        ], 400);
    }

    private function settleWithdrawal(string $txnRefNo, bool $successful, array $response): void
    {
        DB::transaction(function () use ($txnRefNo, $successful, $response) {
            $transaction = CommissionLog::where('transaction_reference', $txnRefNo)
                ->where('transaction_type', 'withdrawal')
                ->lockForUpdate()
                ->first();

            if (!$transaction || $transaction->status !== 'pending') {
                return;
            }

            if ($successful) {
                $transaction->update([
                    'status' => 'success',
                    'response_data' => $response,
                    'completed_at' => now(),
                ]);
                return;
            }

            $wallet = Wallet::where('user_id', $transaction->user_id)
                ->lockForUpdate()
                ->first();

            if ($wallet) {
                $wallet->amount += (float) $transaction->commission_deducted;
                $wallet->save();
            }

            $transaction->update([
                'status' => 'failed',
                'new_balance' => $wallet?->amount ?? $transaction->new_balance,
                'response_data' => $response,
                'completed_at' => now(),
            ]);
        });
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

        if (!$this->jazzCash->validateResponseHash($response)) {
            Log::warning('JazzCash refund response signature validation failed.', [
                'reference' => $request->txn_ref_no,
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => 'Response signature verification failed.',
                'data' => $response,
            ], 400);
        }

        // Update transaction
        $transaction = CommissionLog::where('transaction_reference', $request->txn_ref_no)->first();
        if ($transaction) {
            $transaction->update([
                'response_data' => $response,
            ]);

            if ($this->jazzCash->classifyResponse($response) === 'success') {
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

        $responseClassification = $this->jazzCash->classifyResponse($response);

        if ($responseClassification === 'success') {
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

        if ($responseClassification === 'pending') {
            return response()->json([
                'status' => 'pending',
                'message' => $response['pp_ResponseMessage'] ?? 'Balance inquiry is pending.',
                'data' => $response,
            ]);
        }

        return response()->json([
            'status'  => 'failed',
            'message' => $response['pp_ResponseMessage'] ?? 'Unable to fetch balance.',
            'data'    => $response
        ], 400);
    }
}
