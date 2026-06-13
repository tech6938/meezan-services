<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ReferralLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function __construct(protected ReferralService $referralService)
    {
    }

    private function currentUser(): User
    {
        $user = Auth::guard('api')->user();

        abort_unless($user instanceof User, 403, 'Referral features are available for customers only.');

        return $user;
    }

    public function myReferralCode()
    {
        $user = $this->currentUser();
        $settings = Setting::first();

        return response()->json([
            'status' => true,
            'data' => [
                'referral_code' => $user->referral_code,
                'referral_link' => $settings && $settings->app_url
                    ? rtrim($settings->app_url, '/') . '?referral_code=' . $user->referral_code
                    : null,
                'referral_enabled' => (bool) (optional($settings)->referral_enabled ?? false),
                'direct_referrals' => (int) ($user->referral_total_referrals ?? 0),
            ],
        ]);
    }

    public function myReferralTree()
    {
        $user = $this->currentUser();

        return response()->json([
            'status' => true,
            'data' => $this->referralService->buildTree($user, 3),
        ]);
    }

    public function myReferralEarnings()
    {
        $user = $this->currentUser();
        $summary = $this->referralService->summarizeEarnings($user);

        return response()->json([
            'status' => true,
            'data' => [
                'referral_code' => $user->referral_code,
                'total_earned' => $summary['total_earned'],
                'balance' => $summary['balance'],
                'total_referrals' => $summary['total_referrals'],
                'levels' => $summary['levels'],
            ],
        ]);
    }

    public function myReferralHistory(Request $request)
    {
        $user = $this->currentUser();

        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = ReferralLog::with([
            'referredUser:id,name,phone,image,referral_code',
            'booking:id,order_no,price,status,created_at',
        ])->where('referrer_user_id', $user->id)
            ->orderByDesc('created_at');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = (int) ($request->input('per_page', 15));
        $logs = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $logs->getCollection()->map(function (ReferralLog $log) {
                return [
                    'id' => $log->id,
                    'booking_id' => $log->booking_id,
                    'level' => $log->level,
                    'referral_code' => $log->referral_code,
                    'commission_type' => $log->commission_type,
                    'commission_rate' => (float) $log->commission_rate,
                    'booking_amount' => (float) $log->booking_amount,
                    'earned_amount' => (float) $log->earned_amount,
                    'status' => $log->status,
                    'created_at' => $log->created_at,
                    'referred_user' => $log->referredUser ? [
                        'id' => $log->referredUser->id,
                        'name' => $log->referredUser->name,
                        'phone' => $log->referredUser->phone,
                        'image' => $log->referredUser->image ? url('profiles/' . $log->referredUser->image) : null,
                        'referral_code' => $log->referredUser->referral_code,
                    ] : null,
                    'booking' => $log->booking ? [
                        'id' => $log->booking->id,
                        'order_no' => $log->booking->order_no,
                        'price' => $log->booking->price,
                        'status' => $log->booking->status,
                        'created_at' => $log->booking->created_at,
                    ] : null,
                ];
            })->values(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}
