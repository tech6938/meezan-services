<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookingRequest;

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
        $data = BookingRequest::with('provider')->where('status','cancel')->get(); // Include provider relationship
        return view('booking.cancel', compact('data'));
    }
}
