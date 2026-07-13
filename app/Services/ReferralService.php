<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\ReferralLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralService
{
    public function generateUniqueReferralCode(int $length = 8): string
    {
        do {
            $code = 'MS-REF-' . Str::upper(Str::random($length));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    public function findByReferralCode(?string $code): ?User
    {
        if (!$code) {
            return null;
        }

        return User::where('referral_code', $code)->first();
    }

    public function getSettings(): Setting
    {
        return Setting::first() ?? new Setting([
            'referral_enabled' => 0,
            'referral_type' => 'percentage',
            'referral_level_1' => 0,
            'referral_level_2' => 0,
            'referral_level_3' => 0,
            'referral_min_amount' => 0,
            'referral_max_amount' => 0,
        ]);
    }

    public function isEnabled(): bool
    {
        $settings = $this->getSettings();

        return (bool) ($settings->referral_enabled ?? false);
    }

    public function assignReferrer(User $user, ?string $referralCode): ?User
    {
        $referrer = $this->findByReferralCode($referralCode);

        if (!$referrer || $referrer->id === $user->id) {
            return null;
        }

        if ($user->referred_by_user_id) {
            return $user->referrer;
        }

        $user->referred_by_user_id = $referrer->id;
        $user->save();

        $referrer->increment('referral_total_referrals');

        return $referrer;
    }

    public function processBookingReferralEarnings(BookingRequest $booking): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => true,
                'processed' => 0,
                'message' => 'Referral system is disabled.',
            ];
        }

        $booking->loadMissing('user.referrer.referrer.referrer');

        $settings = $this->getSettings();
        $bookingAmount = (float) $booking->price;
        $levelOneRate = (float) ($settings->referral_level_1 ?? 0);

        $currentUser = $booking->user;
        $ancestors = [];

        if ($currentUser && $currentUser->referrer) {
            $ancestors[1] = $currentUser->referrer;
        }

        $created = [];

        foreach ($ancestors as $level => $referrer) {
            $baseCommission = $level === 1 ? $levelOneRate : 0;

            if ($baseCommission <= 0) {
                continue;
            }

            $earnedAmount = $this->calculateRewardAmount($bookingAmount, $baseCommission, (string) ($settings->referral_type ?? 'percentage'));

            if ($earnedAmount <= 0) {
                continue;
            }

            $earnedAmount = $this->applyAmountLimits(
                $earnedAmount,
                (float) ($settings->referral_min_amount ?? 0),
                (float) ($settings->referral_max_amount ?? 0)
            );

            $log = ReferralLog::firstOrCreate(
                [
                    'booking_id' => $booking->id,
                    'referrer_user_id' => $referrer->id,
                    'referred_user_id' => $booking->user_id,
                    'level' => $level,
                ],
                [
                    'referral_code' => $referrer->referral_code,
                    'commission_type' => $settings->referral_type ?? 'percentage',
                    'commission_rate' => $baseCommission,
                    'booking_amount' => $bookingAmount,
                    'earned_amount' => $earnedAmount,
                    'status' => 'credited',
                ]
            );

            if ($log->wasRecentlyCreated) {
                $referrer->increment('referral_total_earned', $earnedAmount);
                $referrer->increment('referral_balance', $earnedAmount);

                $created[] = $log;
            }
        }

        return [
            'success' => true,
            'processed' => count($created),
            'logs' => $created,
        ];
    }

    public function buildTree(User $root, int $depth = 1): array
    {
        $root->loadCount('referrals');

        if ($depth < 1) {
            return $this->formatTreeNode($root, 0, []);
        }

        $allUsers = User::select([
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
        ])->orderBy('id')->get();

        $tree = $this->buildForest($allUsers, $root->id, $depth);

        return $this->formatTreeNode($root, 0, $tree);
    }

    public function buildForest(Collection $users, ?int $rootId = null, int $maxDepth = 1): array
    {
        $childrenByParent = $users->groupBy('referred_by_user_id');

        $build = function ($parentId, int $depth) use (&$build, $childrenByParent, $maxDepth) {
            if ($depth > $maxDepth) {
                return [];
            }

            return ($childrenByParent[$parentId] ?? collect())->map(function (User $user) use (&$build, $depth) {
                $children = $build($user->id, $depth + 1);

                return $this->formatTreeNode($user, $depth, $children);
            })->values()->all();
        };

        return $build($rootId, 1);
    }

    public function summarizeEarnings(User $user): array
    {
        $logsQuery = ReferralLog::where('referrer_user_id', $user->id);

        return [
            'total_earned' => (float) ($user->referral_total_earned ?? 0),
            'balance' => (float) ($user->referral_balance ?? 0),
            'total_referrals' => (int) ($user->referral_total_referrals ?? 0),
            'levels' => (clone $logsQuery)
                ->selectRaw('level, COUNT(*) as total_referrals, COALESCE(SUM(earned_amount), 0) as total_earned')
                ->groupBy('level')
                ->orderBy('level')
                ->get(),
        ];
    }

    private function calculateRewardAmount(float $bookingAmount, float $commissionRate, string $commissionType): float
    {
        if ($commissionType === 'fixed') {
            return round($commissionRate, 2);
        }

        return round(($bookingAmount * $commissionRate) / 100, 2);
    }

    private function applyAmountLimits(float $amount, float $minAmount, float $maxAmount): float
    {
        if ($minAmount > 0) {
            $amount = max($amount, $minAmount);
        }

        if ($maxAmount > 0) {
            $amount = min($amount, $maxAmount);
        }

        return round($amount, 2);
    }

    private function formatTreeNode(User $user, int $depth, array $children): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'image' => $user->image ? url('profiles/' . $user->image) : null,
            'referral_code' => $user->referral_code,
            'referred_by_user_id' => $user->referred_by_user_id,
            'total_referrals' => (int) ($user->referral_total_referrals ?? 0),
            'total_earned' => (float) ($user->referral_total_earned ?? 0),
            'balance' => (float) ($user->referral_balance ?? 0),
            'depth' => $depth,
            'children' => $children,
        ];
    }
}
