<?php

namespace App\Http\Controllers;

use App\Exports\ProvidersExport;
use App\Exports\ProvidersMultiSheetExport;
use App\Exports\UsersExport;
use App\Exports\UsersMultiSheetExport;
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
            'day' => ['nullable', 'regex:/^(\d{4}-\d{2}-\d{2}|\d{1,2})$/'],
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|between:1900,2100',
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
        try {
            $provider = Provider::findOrFail($id);
            $providerName = $provider->full_name;
            $provider->delete();

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Provider \"{$providerName}\" has been deleted successfully."
                ]);
            }

            return redirect()->back()->with('success', "Provider \"{$providerName}\" has been deleted successfully.");
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete provider: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete provider: ' . $e->getMessage());
        }
    }
    /**
     * Export users to Excel
     */
    public function exportUsers(Request $request)
    {
        $this->validateExportDateRange($request);

        try {
            return Excel::download(
                UsersExport::fromRequest($request),
                'users_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
            );
        } catch (\Throwable $e) {
            Log::error('User export failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            abort(500, 'User export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export users with multiple sheets (Summary + Details)
     */
    public function exportUsersMultiSheet(Request $request)
    {
        $this->validateExportDateRange($request);

        try {
            return Excel::download(
                UsersMultiSheetExport::fromRequest($request),
                'users_complete_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
            );
        } catch (\Throwable $e) {
            Log::error('User multi-sheet export failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            abort(500, 'User export failed: ' . $e->getMessage());
        }
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

    /**
     * Export providers to Excel with multiple sheets
     */
    public function exportProvidersDetailed(Request $request)
    {
        $this->validateExportDateRange($request);

        try {
            // Get providers with filters
            $query = Provider::query();

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $providers = $query->get();

            // Get booking requests with date filters
            $bookingQuery = \App\Models\BookingRequest::with(['provider', 'user']);

            // Apply date filters
            if ($request->has('start_date') && $request->start_date) {
                $bookingQuery->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date) {
                $bookingQuery->whereDate('created_at', '<=', $request->end_date);
            }

            if ($request->has('day') && $request->day) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->day)) {
                    $bookingQuery->whereDate('created_at', $request->day);
                } elseif (is_numeric($request->day)) {
                    $bookingQuery->whereDay('created_at', intval($request->day));
                }
            }

            if ($request->has('month') && $request->month) {
                $bookingQuery->whereMonth('created_at', intval($request->month));
            }

            if ($request->has('year') && $request->year) {
                $bookingQuery->whereYear('created_at', intval($request->year));
            }

            $bookingRequests = $bookingQuery->get();

            return Excel::download(
                new ProvidersMultiSheetExport($providers, $bookingRequests),
                'providers_complete_report_' . now()->format('Y-m-d') . '.xlsx'
            );
        } catch (\Throwable $e) {
            Log::error('Provider detailed export failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            abort(500, 'Provider export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export providers with multiple sheets (Summary + Details + Pivot)
     */
    public function exportProvidersMultiSheet(Request $request)
    {
        $this->validateExportDateRange($request);

        try {
            // Get providers
            $providers = Provider::query()->get();

            // Get booking requests with filters
            $bookingQuery = BookingRequest::with(['provider', 'user']);

            if ($request->has('start_date') && $request->start_date) {
                $bookingQuery->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date) {
                $bookingQuery->whereDate('created_at', '<=', $request->end_date);
            }

            if ($request->has('month') && $request->month) {
                $bookingQuery->whereMonth('created_at', $request->month);
            }

            if ($request->has('year') && $request->year) {
                $bookingQuery->whereYear('created_at', $request->year);
            }

            $bookingRequests = $bookingQuery->get();

            return Excel::download(
                new ProvidersMultiSheetExport($providers, $bookingRequests),
                'providers_report_' . now()->format('Y-m-d') . '.xlsx'
            );
        } catch (\Throwable $e) {
            Log::error('Multi-sheet export failed', ['error' => $e->getMessage()]);
            abort(500, 'Export failed: ' . $e->getMessage());
        }
    }
}
