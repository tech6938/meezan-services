<?php

namespace App\Http\Controllers;

use App\Exports\BookingsExport;
use App\Exports\BookingsMultiSheetExport;
use App\Models\BookingRequest;
use App\Models\Chat;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class BookingController extends Controller
{
    public function pendingBooking(Request $request)
    {
        $data = $this->applyDateRangeFilter(
            BookingRequest::with(['provider', 'shopkeeper'])->where('status', 'pending'),
            $request
        )->get();

        return view('booking.pending', compact('data'));
    }
    // acceptedbooking
    public function startBooking(Request $request)
    {
        $data = $this->applyDateRangeFilter(
            BookingRequest::with(['provider', 'shopkeeper'])->where('status', 'in_progress'),
            $request
        )->get();

        return view('booking.start', compact('data'));
    }

    // cancelbooking
    public function endBooking(Request $request)
    {
        $data = $this->applyDateRangeFilter(
            BookingRequest::with(['provider', 'shopkeeper'])->where('status', 'complete_booking'),
            $request
        )->get();

        return view('booking.end', compact('data'));
    }
    // bookingStatusUpdate
    public function bookingStatusUpdate(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:booking_requests,id',
            'status' => 'required|in:pending,in_progress,complete_booking,cancel',
        ]);

        $booking = BookingRequest::find($request->booking_id);
        $booking->status = $request->status;
        $booking->save();

        return redirect()->back()->with('success', 'Booking status updated successfully!');
    }
    // all
    public function allBookings(Request $request)
    {
        $data = $this->applyDateRangeFilter(
            BookingRequest::with(['provider', 'shopkeeper']),
            $request
        )->get();
        return view('booking.allbookings', compact('data'));
    }
    //cancel booking
    public function cancelBooking(Request $request)
    {
        $data = $this->applyDateRangeFilter(
            BookingRequest::with(['provider', 'shopkeeper'])->where('status', 'cancel'),
            $request
        )->get();

        return view('booking.cancel', compact('data'));
    }

    protected function applyDateRangeFilter($query, Request $request)
    {
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return $query;
    }

    protected function validateExportDateRange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            abort(422, $validator->errors()->first());
        }
    }


    public function chatBetweenBooking($status, $user_id, $provider_id)
    {
        $booking = BookingRequest::where('user_id', $user_id)
            ->where(function ($q) use ($provider_id) {
                $q->where('provider_id', $provider_id)
                    ->orWhere('shopkeeper_id', $provider_id);
            })
            ->where('status', $status)
            ->first();

        abort_unless($booking, 404);

        $sender = User::find($user_id);
        abort_unless($sender, 404);

        // ✅ FIXED RECEIVER LOGIC
        if ($booking && $booking->provider_id) {
            $receiver = $booking->provider;
        } else {
            $receiver = $booking->shopkeeper ?? null;
        }

        abort_unless($receiver, 404);

        $messages = Chat::betweenParticipants(
            ['id' => $sender->id, 'type' => User::class],
            ['id' => $receiver->id, 'type' => get_class($receiver)]
        )
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('booking.chat', compact('messages', 'sender', 'receiver', 'status'));
    }

    public function bookingDetail($booking_id)
    {
        $booking = BookingRequest::with([
            'user',
            'provider',
            'shopkeeper',
            'serviceRequest.category',
            'serviceRequest.subCategory',
            'serviceRequest.address',
        ])->findOrFail($booking_id);

        // Receiver is provider if exists, otherwise shopkeeper
        $receiver = $booking->provider ?? $booking->shopkeeper;
        // dd($booking->serviceRequest->lang);

        return view('booking.detail', compact('booking', 'receiver'));
    }

    /**
     * Export bookings to Excel
     */
    public function exportBookings(Request $request)
    {
        $this->validateExportDateRange($request);

        return Excel::download(
            BookingsExport::fromRequest($request),
            'bookings_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    /**
     * Export bookings with multi-sheet (Summary + Details)
     */
    public function exportBookingsMultiSheet(Request $request)
    {
        try {
            return Excel::download(
                BookingsMultiSheetExport::fromRequest($request),
                'bookings_complete_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
            );
        } catch (\Throwable $e) {
            Log::error('Bookings export failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            abort(500, 'Bookings export failed: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Methods (JSON Response)
    |--------------------------------------------------------------------------
    */

    /**
     * Preview bookings data (returns JSON for preview modal)
     */
    public function previewBookings(Request $request)
    {
        $this->validateExportDateRange($request);

        try {
            $query = BookingRequest::with(['provider', 'user', 'shopkeeper']);

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('booking_no', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('provider', function ($q2) use ($search) {
                            $q2->where('full_name', 'like', "%{$search}%");
                        });
                });
            }

            // Apply date range filter
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Limit results for preview (50 records)
            $bookings = $query->orderBy('created_at', 'desc')->limit(50)->get();

            // Format data for preview
            $previewData = $bookings->map(function ($booking, $index) {
                $statusLabels = [
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'complete_booking' => 'Complete',
                    'cancel' => 'Cancelled',
                ];

                return [
                    'Sr. No' => $index + 1,
                    'Booking ID' => $booking->id,
                    'Booking No' => $booking->booking_no ?? 'N/A',
                    'Customer' => $booking->user->name ?? 'N/A',
                    'Provider' => $booking->provider->full_name ?? $booking->shopkeeper->name ?? 'N/A',
                    'Price' => 'PKR ' . number_format($booking->price ?? 0, 2),
                    'Payment Method' => $booking->cash_on_delivery == 0 ? 'Online' : 'Cash on Delivery',
                    'Status' => $statusLabels[$booking->status] ?? ucfirst(str_replace('_', ' ', $booking->status)),
                    'Booking Date' => $booking->created_at ? $booking->created_at->format('Y-m-d H:i') : 'N/A',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $previewData,
                'total' => $bookings->count(),
                'message' => 'Preview showing first 50 records'
            ]);
        } catch (\Throwable $e) {
            Log::error('Booking preview failed', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
