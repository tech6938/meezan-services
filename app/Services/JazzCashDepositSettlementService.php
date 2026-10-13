<?php

namespace App\Services;

use App\Models\CommissionLog;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class JazzCashDepositSettlementService
{
    public function isSuccessfulResponse(array $response): bool
    {
        return ($response['pp_ResponseCode'] ?? '') === '121'
            || ($response['pp_PaymentResponseCode'] ?? '') === '121'
            || strtolower((string) ($response['pp_Status'] ?? '')) === 'completed';
    }

    public function settle(array $response, ?string $reference = null): bool
    {
        $reference = $reference ?? ($response['pp_TxnRefNo'] ?? null);

        if (!$reference || !$this->isSuccessfulResponse($response)) {
            return false;
        }

        return DB::transaction(function () use ($reference, $response) {
            $transaction = CommissionLog::query()
                ->where('transaction_reference', $reference)
                ->where('transaction_type', 'deposit')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                return false;
            }

            if ($transaction->status === 'success') {
                return true;
            }

            if ($transaction->status !== 'pending') {
                return false;
            }

            $wallet = Wallet::query()
                ->where('provider_id', $transaction->provider_id)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                $wallet = Wallet::create([
                    'provider_id' => $transaction->provider_id,
                    'amount' => 0,
                ]);
            }

            $oldBalance = (float) $wallet->amount;
            $newBalance = $oldBalance + (float) $transaction->booking_price;

            $wallet->amount = $newBalance;
            $wallet->save();

            $transaction->update([
                'status' => 'success',
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance,
                'response_data' => $response,
                'completed_at' => now(),
            ]);

            return true;
        });
    }
}
