<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Chat;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function pendingBooking()
    {
        $data = BookingRequest::with(['provider', 'shopkeeper'])
            ->where('status', 'pending')
            ->get();

        return view('booking.pending', compact('data'));
    }
    // acceptedbooking
    public function startBooking()
    {
        $data = BookingRequest::with(['provider', 'shopkeeper'])
            ->where('status', 'in_progress')
            ->get();

        return view('booking.start', compact('data'));
    }

    // cancelbooking
    public function endBooking()
    {
        $data = BookingRequest::with(['provider', 'shopkeeper'])
            ->where('status', 'complete_booking')
            ->get();

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
    public function allBookings()
    {
        $data = BookingRequest::with(['provider', 'shopkeeper'])->get();
        return view('booking.allbookings', compact('data'));
    }
    //cancel booking
    public function cancelBooking()
    {
        $data = BookingRequest::with(['provider', 'shopkeeper'])
            ->where('status', 'cancel')
            ->get();

        return view('booking.cancel', compact('data'));
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

        $messages = Chat::where(function ($q) use ($user_id, $provider_id) {
            $q->where('sender_id', $user_id)
                ->where('receiver_id', $provider_id);
        })
            ->orWhere(function ($q) use ($user_id, $provider_id) {
                $q->where('sender_id', $provider_id)
                    ->where('receiver_id', $user_id);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        $sender = User::find($user_id);

        // ✅ FIXED RECEIVER LOGIC
        if ($booking && $booking->provider_id) {
            $receiver = $booking->provider;
        } else {
            $receiver = $booking->shopkeeper ?? null;
        }

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

        return view('booking.detail', compact('booking', 'receiver'));
    }
}
