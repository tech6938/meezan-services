<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Provider;
use App\Models\BookingRequest;

class UserProviderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Users Section
    |--------------------------------------------------------------------------
    */

    public function userList()
    {
        $data = User::all();
        // return $data;
        return view('user.user-list', compact('data'));
    }

    public function viewUserDetail($id)
    {
        $user = User::with(['serviceRequests', 'bookings.provider'])->findOrFail($id);
        return view('user.viewUserDetail', compact('user'));
    }

    public function updateUserStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'status'  => 'required|in:blocked,unblocked',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->status = $request->status;
        $user->save();

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    public function userDestroy($id)
    {
        $user = User::findOrFail($id);

        // if ($user->bookingRequests()->exists()) {
        //     return redirect()->back()->with('error', 'Cannot delete provider with bookings.');
        // }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }


    /*
    |--------------------------------------------------------------------------
    | Providers Section
    |--------------------------------------------------------------------------
    */

    public function approvedProviders()
    {
        $data = Provider::where('status', 'approved')->get();
        return view('providers.approved', compact('data'));
    }

    public function blockedProviders()
    {
        $data = Provider::where('status', 'blocked')->get();
        return view('providers.blocked', compact('data'));
    }

    public function suspendedProviders()
    {
        $data = Provider::where('status', 'suspend')->get();
        return view('providers.suspended', compact('data'));
    }

    public function pendingProviders()
    {
        $data = Provider::where('status', 'pending')->get();
        return view('providers.pending', compact('data'));
    }

    public function allProviders()
    {
        $data = Provider::all();
        return view('providers.allproviders', compact('data'));
    }

    public function viewProviderDetail($id)
    {
        $provider = Provider::with(['bookingRequests.user'])->findOrFail($id);
        return view('providers.viewProviderDetail', compact('provider'));
    }

    public function statusUpdate(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'status'      => 'required',
        ]);

        $provider = Provider::findOrFail($request->provider_id);
        $provider->status = $request->status;
        $provider->save();

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    public function destroy($id)
    {
        $shopKeeper = Provider::with('bookingRequests')->findOrFail($id);

        // if ($shopKeeper->bookingRequests()->exists()) {
        //     return redirect()->back()->with('error', 'Cannot delete provider with bookings.');
        // }

        $shopKeeper->delete();

        return redirect()->back()->with('success', 'Provider deleted successfully.');
    }
}
