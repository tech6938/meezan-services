<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Chat;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;
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
}
