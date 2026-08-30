<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function deposit(Request $request)
    {
        try {
            $providerId = Auth::guard('provider-api')->id();
            $shopkeeperId = Auth::guard('shopkeeper-api')->id();

            $request->validate([
                'amount' => 'required|numeric|min:1',
                'gateway' => 'required|string',
                'transaction_type' => 'required|in:debit,credit',
            ]);

            // Get or create wallet
            $wallet = Wallet::firstOrCreate(
                ['provider_id' => $providerId, 'shopkeeper_id' => $shopkeeperId],
                ['amount' => 0]
            );

            $preBalance = $wallet->amount;
            $amount = $request->amount;

            // Handle debit / credit
            if ($request->transaction_type === 'debit') {
                if ($wallet->amount < $amount) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient wallet balance',
                    ], 422);
                }
                $wallet->amount -= $amount;
            } else {
                $wallet->amount += $amount;
            }

            $wallet->save();
            $postBalance = $wallet->amount;

            // Generate 14-digit random transaction ID
            $transactionId = (string) random_int(10000000000000, 99999999999999);

            $transaction = Deposit::create([
                'provider_id' => $providerId ?? null,
                'shopkeeper_id' => $shopkeeperId ?? null,
                'transaction_id' => $transactionId,
                'gateway' => $request->gateway,
                'transaction_type' => $request->transaction_type,
                'amount' => $amount,
                'pre_balance' => $preBalance,
                'post_balance' => $postBalance,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Transaction successful',
                'data' => $transaction,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    // myWallet
    public function myWallet()
    {
        try {
            $auth_id = Auth::guard('provider-api')->id();
            $data = Wallet::where('provider_id', $auth_id)->get();
            if (!$data) {
                return response()->json([
                    'status' => true,
                    'message' => 'Your Dont Have Wallet',
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Wallet detials fetched successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }


    // transactionHistory
    public function transactionHistory()
    {
        try {
            $auth_id = Auth::guard('provider-api')->id();

            // Get deposits
            $deposits = Deposit::where('provider_id', $auth_id)->get()->map(function ($deposit) {
                $createdAt = $deposit->created_at
                    ? Carbon::parse($deposit->created_at)->setTimezone(config('app.timezone', 'Asia/Karachi'))
                    : null;

                return [
                    'gateway' => $deposit->gateway,
                    'transaction_id' => $deposit->transaction_id,
                    'transaction_type' => $deposit->transaction_type ?? 'credit',
                    'amount' => (float) $deposit->amount,
                    'date' => $createdAt ? $createdAt->format('d M Y') : null,
                    'time' => $createdAt ? $createdAt->format('h:i A') : null,
                ];
            });

            // Get commission logs using query builder
            $commissions = DB::table('commission_logs')
                ->where('provider_id', $auth_id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($commission) {
                    $createdAt = $commission->created_at
                        ? Carbon::parse($commission->created_at)->setTimezone(config('app.timezone', 'Asia/Karachi'))
                        : null;

                    return [
                        'gateway' => 'Commission',
                        'transaction_id' => 'COM-' . $commission->id,
                        'transaction_type' => 'debit',
                        'amount' => (float) ($commission->commission_deducted ?? 0),
                        'date' => $createdAt ? $createdAt->format('d M Y') : null,
                        'time' => $createdAt ? $createdAt->format('h:i A') : null,
                    ];
                });

            // Combine and sort
            $allTransactions = $deposits->concat($commissions);
            $sortedTransactions = $allTransactions->sortByDesc(function ($item) {
                return $item['date'] . ' ' . $item['time'];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Your Transaction History',
                'provider_id' => $auth_id,
                'data' => $sortedTransactions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
