<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\Commission;
use App\Models\Wallet;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    /**
     * Process commission deduction when booking is completed
     *
     * @param BookingRequest $booking
     * @return array
     */
    public function processCommissionDeduction(BookingRequest $booking)
    {
        $startedTransaction = false;

        try {
            if (DB::transactionLevel() === 0) {
                DB::beginTransaction();
                $startedTransaction = true;
            }

            $serviceRequest = ServiceRequest::find($booking->request_id);

            if (!$serviceRequest) {
                throw new \Exception('Service request not found for this booking.');
            }

            if (!$serviceRequest->subcat_id) {
                throw new \Exception('Subcategory is missing for this booking request.');
            }

            if ($booking->price === null || (float) $booking->price <= 0) {
                throw new \Exception('A valid booking price is required before commission can be deducted.');
            }

            // Get commission details for the subcategory
            $commission = $this->getCommissionDetails($serviceRequest->subcat_id);

            if (!$commission) {
                // No commission defined, skip deduction
                if ($startedTransaction) {
                    DB::commit();
                }
                return [
                    'success' => true,
                    'message' => 'No commission defined for this subcategory.',
                    'commission_deducted' => 0,
                    'old_balance' => null,
                    'new_balance' => null,
                ];
            }

            // Calculate commission amount
            $commissionAmount = $this->calculateCommissionAmount($commission, $booking->price);

            if ($commissionAmount < 0) {
                throw new \Exception('Calculated commission amount is invalid.');
            }

            // Determine who to deduct from (provider or shopkeeper)
            $wallet = $this->getWallet($booking);

            if (!$wallet) {
                throw new \Exception('Wallet not found for the service provider.');
            }

            // Deduct commission from wallet
            $oldBalance = (float) $wallet->amount;
            $wallet->amount -= $commissionAmount;
            $wallet->save();

            // Log the commission deduction (optional - create a commission_logs table)
            $this->logCommissionDeduction([
                'booking_id' => $booking->id,
                'provider_id' => $booking->provider_id,
                'shopkeeper_id' => $booking->shopkeeper_id,
                'sub_category_id' => $serviceRequest->subcat_id,
                'commission_type' => $commission->type,
                'commission_rate' => $commission->amount,
                'booking_price' => $booking->price,
                'commission_deducted' => $commissionAmount,
                'old_balance' => $oldBalance,
                'new_balance' => $wallet->amount,
            ]);

            if ($startedTransaction) {
                DB::commit();
            }

            return [
                'success' => true,
                'message' => 'Commission deducted successfully.',
                'commission_deducted' => $commissionAmount,
                'old_balance' => $oldBalance,
                'new_balance' => $wallet->amount
            ];

        } catch (\Exception $e) {
            if ($startedTransaction && DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Commission deduction failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'commission_deducted' => 0
            ];
        }
    }

    /**
     * Get commission details for a subcategory
     *
     * @param int $subCategoryId
     * @return Commission|null
     */
    private function getCommissionDetails($subCategoryId)
    {
        // First try to get commission for specific subcategory
        $commission = Commission::where('sub_category_id', $subCategoryId)->first();

        if ($commission) {
            return $commission;
        }

        // If no subcategory commission, check for main category commission
        $subCategory = \App\Models\SubCategory::find($subCategoryId);
        if ($subCategory && $subCategory->cat_id) {
            $commission = Commission::where('main_category_id', $subCategory->cat_id)
                ->whereNull('sub_category_id')
                ->first();
        }

        return $commission;
    }

    /**
     * Calculate commission amount based on type
     *
     * @param Commission $commission
     * @param float $bookingPrice
     * @return float
     */
    private function calculateCommissionAmount(Commission $commission, $bookingPrice)
    {
        if ($commission->type === 'percentage') {
            return round(($commission->amount / 100) * $bookingPrice, 2);
        } else {
            // Fixed amount
            return round((float) $commission->amount, 2);
        }
    }

    /**
     * Get wallet for provider or shopkeeper
     *
     * @param BookingRequest $booking
     * @return Wallet|null
     */
    private function getWallet(BookingRequest $booking)
    {
        // Check if provider exists
        if ($booking->provider_id) {
            return Wallet::where('provider_id', $booking->provider_id)->lockForUpdate()->first();
        }

        // Check if shopkeeper exists
        if ($booking->shopkeeper_id) {
            return Wallet::where('shopkeeper_id', $booking->shopkeeper_id)->lockForUpdate()->first();
        }

        return null;
    }

    /**
     * Log commission deduction
     *
     * @param array $data
     * @return void
     */
    private function logCommissionDeduction(array $data)
    {
        // Create commission_logs table if not exists
        try {
            DB::table('commission_logs')->insert([
                'booking_id' => $data['booking_id'],
                'provider_id' => $data['provider_id'],
                'shopkeeper_id' => $data['shopkeeper_id'],
                'sub_category_id' => $data['sub_category_id'],
                'commission_type' => $data['commission_type'],
                'commission_rate' => $data['commission_rate'],
                'booking_price' => $data['booking_price'],
                'commission_deducted' => $data['commission_deducted'],
                'old_balance' => $data['old_balance'],
                'new_balance' => $data['new_balance'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log commission deduction: ' . $e->getMessage());
        }
    }
}
