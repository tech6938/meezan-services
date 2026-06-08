<?php

namespace App\Exports;

use App\Models\BookingRequest;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BookingsMultiSheetExport implements WithMultipleSheets
{
    protected $bookings;
    protected $filters;

    public function __construct($bookings, $filters = [])
    {
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
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        $query = BookingRequest::with(['user', 'provider', 'shopkeeper', 'serviceRequest']);

        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('provider', function ($q2) use ($search) {
                    $q2->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('order_no', 'like', "%{$search}%");
            });
        }

        // Apply date filters
        self::applyDateFilters($query, $filters);

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $bookings = $query->get();

        return new self($bookings, $filters);
    }

    protected static function applyDateFilters($query, $filters)
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $day = $filters['day'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;

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
    }

    public function sheets(): array
    {
        return [
            '📋 Bookings Summary' => new BookingSummarySheet($this->bookings, $this->filters),
            '💰 Bookings Details' => new BookingDetailsSheet($this->bookings, $this->filters),
        ];
    }
}
