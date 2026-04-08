<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetOtpMail;

class ResetPassController extends Controller
{
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:user,provider,shopkeeper',
        ]);

        $model = $this->resolveModel($request->role);

        $account = $model::where('email', $request->email)->first();

        if (!$account) {
            return response()->json([
                'status' => false,
                'message' => 'Email not found'
            ], 404);
        }

        $otp = rand(100000, 999999);

        PasswordReset::updateOrCreate(
            [
                'auth_id' => $account->id,
                'role' => $request->role,
            ],
            [
                'email' => $request->email,
                'otp' => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
            ]
        );

        // Send email
        Mail::to($request->email)->send(
            new ResetOtpMail($otp, $request->role)
        );

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully'
        ]);
    }
    // ---------------------------------------- VERIFY OTP ------------------------------
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:user,provider,shopkeeper',
            'otp' => 'required',
        ]);

        $reset = PasswordReset::where('email', $request->email)
            ->where('role', $request->role)
            ->first();

        if (
            !$reset ||
            !Hash::check($request->otp, $reset->otp) ||
            now()->gt($reset->expires_at)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully'
        ]);
    }

    // ---------------------------------------- RESET PASSWORD ------------------------------
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:user,provider,shopkeeper',
            'otp' => 'required',
            'password' => 'required|min:6'
        ]);

        $reset = PasswordReset::where('email', $request->email)
            ->where('role', $request->role)
            ->first();

        if (
            !$reset ||
            !Hash::check($request->otp, $reset->otp) ||
            now()->gt($reset->expires_at)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        $model = $this->resolveModel($request->role);

        $account = $model::find($reset->auth_id);

        if (!$account) {
            return response()->json([
                'status' => false,
                'message' => 'Account not found'
            ], 404);
        }

        $account->update([
            'password' => Hash::make($request->password)
        ]);

        $reset->delete(); // cleanup

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully'
        ]);
    }

    private function resolveModel(string $role)
    {
        return match ($role) {
            'user' => \App\Models\User::class,
            'provider' => \App\Models\Provider::class,
            'shopkeeper' => \App\Models\ShopKeeper::class,
            default => throw new \Exception('Invalid role'),
        };
    }
}
