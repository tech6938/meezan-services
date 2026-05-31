<?php

namespace App\Http\Controllers;

use App\Exports\ProvidersExport;
use App\Exports\UsersExport;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class UserProviderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Users Section
    |--------------------------------------------------------------------------
    */
    public function userList(Request $request)
    {
        $data = $this->applyDateRangeFilter(User::query(), $request)->get();
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

    /*
    |--------------------------------------------------------------------------
    | Providers Section
    |--------------------------------------------------------------------------
    */

    public function approvedProviders(Request $request)
    {
        $data = $this->applyDateRangeFilter(Provider::where('status', 'approved'), $request)->get();
        return view('providers.approved', compact('data'));
    }

    public function blockedProviders(Request $request)
    {
        $data = $this->applyDateRangeFilter(Provider::where('status', 'blocked'), $request)->get();
        return view('providers.blocked', compact('data'));
    }

    public function suspendedProviders(Request $request)
    {
        $data = $this->applyDateRangeFilter(Provider::where('status', 'suspend'), $request)->get();
        return view('providers.suspended', compact('data'));
    }

    public function pendingProviders(Request $request)
    {
        $data = $this->applyDateRangeFilter(Provider::where('status', 'pending'), $request)->get();
        return view('providers.pending', compact('data'));
    }

    public function allProviders(Request $request)
    {
        $data = $this->applyDateRangeFilter(Provider::query(), $request)->get();
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
        /**
     * Export users to Excel
     */
    public function exportUsers(Request $request)
    {
        $this->validateExportDateRange($request);

        return Excel::download(
            UsersExport::fromRequest($request),
            'users_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    /**
     * Export providers to Excel
     */
    public function exportProviders(Request $request)
    {
        $this->validateExportDateRange($request);

        try {
            return Excel::download(
                ProvidersExport::fromRequest($request),
                'providers_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
            );
        } catch (\Throwable $e) {
            Log::error('Provider export failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            abort(500, 'Provider export failed: ' . $e->getMessage());
        }
    }
}
