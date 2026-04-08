<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Chat;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // pendingBooking
    public function pendingBooking()
    {
        $data = BookingRequest::with('provider')->where('status', 'pending')->get();
        return view('booking.pending', compact('data'));
    }
    // acceptedbooking
    public function startBooking()
    {
        $data = BookingRequest::with('provider')->where('status', 'start')->get();
        return view('booking.start', compact('data'));
    }

    // cancelbooking
    public function endBooking()
    {
        $data = BookingRequest::with('provider')->where('status', 'end')->get();
        return view('booking.end', compact('data'));
    }
    // bookingStatusUpdate
    public function bookingStatusUpdate(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:booking_requests,id',
            'status' => 'required|in:pending,end,start,cancel',
        ]);

        $booking = BookingRequest::find($request->booking_id);
        $booking->status = $request->status;
        $booking->save();

        return redirect()->back()->with('success', 'Booking status updated successfully!');
    }
    // all
    // BookingController.php
    public function allBookings()
    {
        $data = BookingRequest::with('provider')->get(); // Include provider relationship
        return view('booking.allbookings', compact('data'));
    }
    //cancel booking
    public function cancelBooking()
    {
        $data = BookingRequest::with('provider')->where('status', 'cancel')->get(); // Include provider relationship
        return view('booking.cancel', compact('data'));
    }

    public function chatBetweenBooking($status, $user_id, $provider_id)
    {
        // Get booking based on status (optional but recommended)
        $booking = BookingRequest::where('user_id', $user_id)
            ->where('provider_id', $provider_id)
            ->where('status', $status)
            ->first();

        // If booking not found, still allow chat (optional logic)

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
        $receiver = Provider::find($provider_id);

        return view('booking.chat', compact('messages', 'sender', 'receiver', 'status'));
    }
}
