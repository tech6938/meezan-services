<?php

namespace App\Exports;

use App\Exports\UserDetailsSheet;
use App\Exports\UserStatisticsSheet;
use App\Exports\UserSummarySheet;
use App\Models\BookingRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UsersMultiSheetExport implements WithMultipleSheets
{
    protected $users;
    protected $bookings;
    protected $filters;

    public function __construct($users, $bookings, $filters = [])
    {
        $this->users = $users;
        $this->bookings = $bookings;
        $this->filters = $filters;
    }

    public static function fromRequest(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'day' => $request->input('day'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ];

        // Build date filter callback
        $dateFilter = self::createDateFilter($filters);

        // Get users with aggregated data
        $query = User::query();

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Add booking counts with date filter
        $query->withCount([
            'bookingRequests as total_orders_count' => $dateFilter,
            'bookingRequests as accepted_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->whereIn('status', ['accept', 'accepted']);
            },
            'bookingRequests as pending_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'pending');
            },
            'bookingRequests as in_progress_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'in_progress');
            },
            'bookingRequests as cancelled_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->whereIn('status', ['cancel', 'cancelled']);
            },
            'bookingRequests as completed_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->whereIn('status', ['complete_booking', 'completed']);
            },
        ]);

        // Add sum for total amount spent by user
        $query->withSum([
            'bookingRequests as total_amount_spent' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->whereIn('status', ['complete_booking', 'completed']);
            }
        ], 'price');

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->get();

        // Get detailed bookings with user and provider
        $bookingQuery = BookingRequest::with(['user', 'provider']);
        $dateFilter($bookingQuery);

        // Apply search filter for bookings
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $bookingQuery->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('provider', function ($q2) use ($search) {
                    $q2->where('full_name', 'like', "%{$search}%");
                })->orWhere('booking_no', 'like', "%{$search}%");
            });
        }

        $bookings = $bookingQuery->orderBy('created_at', 'desc')->get();

        return new self($users, $bookings, $filters);
    }

    protected static function createDateFilter($filters)
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $day = $filters['day'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;

        return function ($query) use ($startDate, $endDate, $day, $month, $year) {
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
                return;
            }

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            if (!$startDate && !$endDate) {
                if ($day) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
                        $query->whereDate('created_at', $day);
                    } elseif (is_numeric($day)) {
                        $query->whereDay('created_at', intval($day));
                    }
                }

                if ($month) {
                    $query->whereMonth('created_at', intval($month));
                }

                if ($year) {
                    $query->whereYear('created_at', intval($year));
                }
            }
        };
    }

    public function sheets(): array
    {
        return [
            '📊 Summary (User-wise)' => new UserSummarySheet($this->users, $this->filters),
            '📋 Details (Booking-wise)' => new UserDetailsSheet($this->bookings, $this->filters),
            // '📈 Statistics' => new UserStatisticsSheet($this->users, $this->bookings, $this->filters),
        ];
    }
}
