<?php

namespace App\Http\Controllers;

use App\Models\ReferralLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function settings()
    {
        $settings = Setting::first() ?? new Setting();

        return view('referrals.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'referral_enabled' => 'nullable|in:0,1',
            'referral_type' => 'required|in:fixed,percentage',
            'referral_level_1' => 'required|numeric|min:0',
            'referral_min_amount' => 'nullable|numeric|min:0',
            'referral_max_amount' => 'nullable|numeric|gte:referral_min_amount',
        ]);

        $settings = Setting::firstOrNew(['id' => 5]);

        if (!$settings->exists) {
            $settings->app_url = '';
            $settings->whatsapp = '';
            $settings->privacy_policy = '';
            $settings->save();
        }

        $settings->referral_enabled = (int) $request->boolean('referral_enabled');
        $settings->referral_type = $request->referral_type;
        $settings->referral_level_1 = $request->referral_level_1;
        $settings->referral_level_2 = 0;
        $settings->referral_level_3 = 0;
        $settings->referral_min_amount = $request->referral_min_amount;
        $settings->referral_max_amount = $request->referral_max_amount;
        $settings->save();

        return redirect()->back()->with('success', 'Referral settings updated successfully.');
    }

    public function tree(Request $request)
    {
        $query = User::withCount('referrals')
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('referral_code', 'like', "%{$search}%");
            });
        }

        $users = $query->get([
            'id',
            'name',
            'phone',
            'image',
            'referral_code',
            'referred_by_user_id',
            'referral_total_referrals',
            'referral_total_earned',
            'referral_balance',
            'created_at',
        ]);

        $tree = $this->referralService->buildForest($users, null, 1);

        $summary = [
            'total_customers' => User::count(),
            'customers_with_code' => User::whereNotNull('referral_code')->count(),
            'direct_referrals' => User::sum('referral_total_referrals'),
            'total_earned' => (float) DB::table('referral_logs')->sum('earned_amount'),
        ];

        return view('referrals.tree', compact('tree', 'summary'));
    }

    public function customerEarnings(Request $request)
    {
        $usersQuery = User::withCount('referrals')
            ->withSum('referralLogs as referral_logs_total_earned', 'earned_amount')
            ->orderByDesc('referral_total_earned');

        if ($request->filled('search')) {
            $search = $request->search;
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('referral_code', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->paginate(15)->withQueryString();

        return view('referrals.customer_earnings', compact('users'));
    }

    public function commissionLogs(Request $request)
    {
        $query = ReferralLog::with([
            'referrer:id,name,phone,image,referral_code',
            'referredUser:id,name,phone,image,referral_code',
            'booking:id,order_no,price,status,created_at',
        ])->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('referrer', function ($referrerQuery) use ($search) {
                    $referrerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('referral_code', 'like', "%{$search}%");
                })->orWhereHas('referredUser', function ($referredQuery) use ($search) {
                    $referredQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('referral_code', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(15)->withQueryString();

        $summary = [
            'total_logs' => ReferralLog::count(),
            'total_payout' => (float) ReferralLog::sum('earned_amount'),
            'level_1' => (float) ReferralLog::where('level', 1)->sum('earned_amount'),
            'level_2' => (float) ReferralLog::where('level', 2)->sum('earned_amount'),
            'level_3' => (float) ReferralLog::where('level', 3)->sum('earned_amount'),
        ];

        return view('referrals.commission_logs', compact('logs', 'summary'));
    }

    public function reports(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $logQuery = ReferralLog::query();

        if ($startDate) {
            $logQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $logQuery->whereDate('created_at', '<=', $endDate);
        }

        $logs = $logQuery->get();

        $levelSummary = $logs->groupBy('level')->map(function ($items, $level) {
            return [
                'level' => $level,
                'count' => $items->count(),
                'amount' => (float) $items->sum('earned_amount'),
            ];
        })->values();

        $monthlySummary = $logs->groupBy(function ($log) {
            return optional($log->created_at)->format('Y-m');
        })->map(function ($items, $month) {
            return [
                'month' => $month,
                'count' => $items->count(),
                'amount' => (float) $items->sum('earned_amount'),
            ];
        })->values();

        $summary = [
            'total_logs' => $logs->count(),
            'total_payout' => (float) $logs->sum('earned_amount'),
            'active_customers' => User::whereNotNull('referral_code')->count(),
            'referral_enabled' => (bool) (optional(Setting::first())->referral_enabled ?? false),
        ];

        return view('referrals.reports', compact('summary', 'levelSummary', 'monthlySummary'));
    }










    /**
     * Simple landing page for referral links
     */
    public function landing($code)
    {
        // Find the referrer
        $referrer = User::where('referral_code', $code)->first();

        if (!$referrer) {
            return view('referral.invalid', ['code' => $code]);
        }

        // Log the click
        Log::info('Referral link clicked', [
            'code' => $code,
            'referrer_id' => $referrer->id,
            'ip' => request()->ip()
        ]);

        // Simple view with app deep link and fallback
        return view('referral.landing', [
            'code' => $code,
            'referrer_name' => $referrer->name,
            'app_scheme' => 'meezanservices://referral?code=' . $code,
            'playstore_url' => 'https://play.google.com/store/apps/details?id=com.meezanservices.app&referrer=' . $code,
            'appstore_url' => 'https://apps.apple.com/app/idYOUR_APP_ID?referrer=' . $code,
        ]);
    }

    /**
     * Simple API endpoint for app to validate referral code
     */
    public function validateCode(Request $request)
    {
        $code = $request->input('code');

        // Validate code exists
        $referrer = User::where('referral_code', $code)->first();

        if (!$referrer) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid referral code'
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'referrer_id' => $referrer->id,
            'referrer_name' => $referrer->name,
        ]);
    }

    /**
     * Simple API endpoint for applying referral code
     */
    public function apply(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'referral_code' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $referrer = User::where('referral_code', $request->referral_code)->first();

        // Check if valid
        if (!$referrer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code'
            ]);
        }

        // Check self-referral
        if ($referrer->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot refer yourself'
            ]);
        }

        // Check already referred
        if ($user->referred_by_user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Already referred'
            ]);
        }

        // Assign referrer
        $user->referred_by_user_id = $referrer->id;
        $user->save();

        // Increment referral count
        $referrer->increment('referral_total_referrals');

        return response()->json([
            'success' => true,
            'message' => 'Referral applied successfully'
        ]);
    }

    /**
     * Get user's referral info (for sharing)
     */
    public function getInfo(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Generate code if not exists
        if (!$user->referral_code) {
            $user->referral_code = $this->referralService->generateUniqueReferralCode();
            $user->save();
        }

        return response()->json([
            'referral_code' => $user->referral_code,
            'referral_link' => config('app.url') . '/referral/' . $user->referral_code,
            'total_referrals' => $user->referral_total_referrals ?? 0,
            'total_earned' => $user->referral_total_earned ?? 0,
        ]);
    }
}
