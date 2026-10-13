<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class JazzCashService
{
    protected string $merchantId;
    protected string $password;
    protected string $salt;
    protected string $merchantMpin;
    protected string $env;

    public function __construct()
    {
        $this->merchantId = config('jazzcash.merchant_id');
        $this->password   = config('jazzcash.password');
        $this->salt       = config('jazzcash.salt');
        $this->merchantMpin = config('jazzcash.merchant_mpin');
        $this->env        = config('jazzcash.env');
    }


    /**
     * Status Inquiry API (v2.0)
     */
    public function inquiryStatus(string $txnRefNo): array
    {
        $endpoint = config("jazzcash.endpoints.{$this->env}.status");

        $payload = [
            'pp_TxnRefNo'   => $txnRefNo,
            'pp_MerchantID' => $this->merchantId,
            'pp_Password'   => $this->password,
        ];

        // Generate Secure Hash
        $payload['pp_SecureHash'] = $this->generateHash($payload);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($endpoint, $payload);

        return $response->json() ?? [];
    }

    /**
     * Calculates HMAC-SHA256 hash using all non-empty fields in alphabetical order.
     */
    public function generateHash(array $params): string
    {
        $filtered = [];

        foreach ($params as $key => $value) {
            // Exclude pp_SecureHash from calculation
            if (strtolower($key) === 'pp_securehash') {
                continue;
            }

            // ONLY include non-empty values in the hash string
            if ($value !== null && $value !== '') {
                $filtered[$key] = (string) $value;
            }
        }

        // 1. Sort by parameter names (keys) in ascending ASCII order
        ksort($filtered, SORT_STRING);

        // 2. Concatenate non-empty values with '&'
        $hashString = implode('&', $filtered);

        // 3. Prepend Integrity Salt
        $finalString = $this->salt . '&' . $hashString;

        // 4. HMAC-SHA256 calculation
        return strtoupper(hash_hmac('sha256', $finalString, $this->salt));
    }

    /**
     * Mobile Wallet Payment Request (v2.0)
     */
    public function payMWallet(
        string $mobileNumber,
        string $cnicLast6,
        float $amount,
        ?string $txnRefNo = null,
        string $billRef = 'INV123'
        ): array {
        $endpoint = config("jazzcash.endpoints.{$this->env}.mwallet");

        $now = Carbon::now('Asia/Karachi');
        // Section 3.5: Must be set to 1 day after the transaction date
        $expiry = $now->copy()->addDay();

        $uniqueTxnRef = $txnRefNo ?? ('T' . $now->format('YmdHis'));

        // Section 3.1: Complete schema required. Keep empty values as ""
        $payload = [
            'pp_Amount'            => (string) ($amount * 100), // Multiply by 100 (Section 3.3)
            'pp_BankID'            => '',
            'pp_BillReference'     => $billRef,
            'pp_CNIC'              => $cnicLast6,
            'pp_Description'       => 'Payment Transaction',
            'pp_Language'          => 'EN',
            'pp_MerchantID'        => $this->merchantId,
            'pp_MobileNumber'      => $mobileNumber,
            'pp_Password'          => $this->password,
            'pp_ProductID'         => '',
            'pp_SubMerchantID'     => '',
            'pp_TxnCurrency'       => 'PKR',
            'pp_TxnDateTime'       => $now->format('YmdHis'),
            'pp_TxnExpiryDateTime' => $expiry->format('YmdHis'),
            'pp_TxnRefNo'          => $uniqueTxnRef,
            'ppmpf_1'              => '',
            'ppmpf_2'              => '',
            'ppmpf_3'              => '',
            'ppmpf_4'              => '',
            'ppmpf_5'              => '',
        ];

        // Hash is generated ONLY from non-empty items inside $payload
        $payload['pp_SecureHash'] = $this->generateHash($payload);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($endpoint, $payload);

        return $response->json();
    }


    /**
     * Validate incoming response or IPN callback hash against locally computed hash.
     */
    public function classifyResponse(array $responsePayload): string
    {
        $paymentCode = trim((string) ($responsePayload['pp_PaymentResponseCode'] ?? ''));
        $responseCode = trim((string) ($responsePayload['pp_ResponseCode'] ?? ''));
        $status = strtolower(trim((string) ($responsePayload['pp_Status'] ?? '')));

        $codes = array_values(array_filter([$paymentCode, $responseCode]));
        foreach ($codes as $code) {
            if (!in_array($code, ['000', '121', '124', '157'], true)) {
                return 'failure';
            }
        }

        if (in_array($status, ['failed', 'cancelled'], true)) {
            return 'failure';
        }

        if (array_intersect($codes, ['124', '157'])) {
            return 'pending';
        }

        if (in_array('121', $codes, true)) {
            return 'success';
        }

        if (in_array('000', $codes, true)) {
            return $status === 'pending' ? 'pending' : 'success';
        }

        return match ($status) {
            'completed' => 'success',
            'pending' => 'pending',
            default => 'failure',
        };
    }

    public function validateResponseHash(array $responsePayload): bool
    {
        $incomingHash = $responsePayload['pp_SecureHash'] ?? '';

        // // Allow API error responses with empty hashes to pass through for code extraction
        // if (empty($incomingHash) && isset($responsePayload['pp_ResponseCode']) && $responsePayload['pp_ResponseCode'] !== '000') {
        //     return true;
        // }

        if (empty($incomingHash)) {
            return false;
        }

        $calculatedHash = $this->generateHash($responsePayload);

        return hash_equals(strtoupper($incomingHash), $calculatedHash);
    }

    /**
     * Merchant Account Balance Inquiry API
     */
    public function getMerchantBalance(): array
    {
        $endpoint = config("jazzcash.endpoints.{$this->env}.merchant_balance");

        $payload = [
            'pp_MerchantID' => $this->merchantId,
            'pp_Password'   => $this->password,
            'pp_TxnDateTime' => now()->tz('Asia/Karachi')->format('YmdHis'),
        ];

        $payload['pp_SecureHash'] = $this->generateHash($payload);

        return Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($endpoint, $payload)->json();
    }

    /**
     * MWallet Refund API
     */
    public function refundTransaction(string $txnRefNo, float $amount): array
    {
        $endpoint = config("jazzcash.endpoints.{$this->env}.refund");

        $payload = [
            'pp_MerchantID'   => $this->merchantId,
            'pp_Password'     => $this->password,
            'pp_TxnRefNo'     => $txnRefNo,
            'pp_Amount'       => (string) ((int) $amount), // Standard PKR string ("100")
            'pp_TxnCurrency'  => 'PKR',
            'pp_MerchantMPIN' => $this->merchantMpin,
            ];

            // Generate SHA256 Secure Hash
            $payload['pp_SecureHash'] = $this->generateHash($payload);
            // dd($payload['pp_SecureHash']);

            $response = Http::withHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

        return $response->json() ?? [];
    }
}
