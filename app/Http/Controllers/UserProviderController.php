<?php

namespace App\Http\Controllers;

use App\Exports\ProvidersExport;
use App\Exports\ProvidersMultiSheetExport;
use App\Exports\UsersExport;
use App\Exports\UsersMultiSheetExport;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $data = $this->applyDateRangeFilter(User::withCount('referrals'), $request)->get();
        return view('user.user-list', compact('data'));
    }

    // public function viewUserDetail($id)
    // {
    //     $user = User::with([
    //         'serviceRequests',
    //         'bookings.provider',
    //         'referrer',
    //         'referrals',
    //         'referralLogs.booking',
    //     ])->withCount('referrals')->findOrFail($id);
    //     return view('user.viewUserDetail', compact('user'));
    // }

    public function viewUserDetail($id)
    {
        $user = User::with([
            'serviceRequests',
            'bookings.provider',
            'bookings' => function ($query) {
                $query->with('serviceRequest');
            },
            'referrer',
            'referrals',
            'referralLogs.booking',
        ])->withCount('referrals')->findOrFail($id);

        // Calculate statistics
        $totalSpent = $user->bookings->where('status', 'complete_booking')->sum('price'); // Only completed bookings sum
        $approvedCount = $user->serviceRequests->where('status', 'approved')->count();
        $pendingCount = $user->serviceRequests->where('status', 'pending')->count();
        $totalBookings = $user->bookings->count();
        $completedBookings = $user->bookings->where('status', 'complete_booking')->count();
        $referralTotalEarned = (float) ($user->referral_total_earned ?? 0);

        return view('user.viewUserDetail', compact('user', 'totalSpent', 'approvedCount', 'pendingCount', 'totalBookings', 'completedBookings', 'referralTotalEarned'));
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
        $provider = Provider::with([
            'bookingRequests.user',
            'bookingRequests.serviceRequest', // Load serviceRequest relationship
            'bookingRequests' => function ($query) {
                $query->with('serviceRequest');
            }
        ])->findOrFail($id);

        // Get all booking requests with their service requests
        $bookingRequests = $provider->bookingRequests;

        // Calculate statistics from booking_requests table
        $totalBookings = $bookingRequests->count();
        $acceptedOrders = $bookingRequests->where('status', 'accept')->count();
        $pendingOrders = $bookingRequests->where('status', 'pending')->count();
        $cancelBookings = $bookingRequests->where('status', 'cancelled')->count();
        $completedBookings = $bookingRequests->where('status', 'complete_booking')->count();

        // Get service request statuses (if you need to show service request specific status)
        $serviceRequestStatuses = $bookingRequests
            ->pluck('serviceRequest')
            ->filter()
            ->groupBy('status')
            ->map(function ($group) {
                return $group->count();
            });
        $serviceRequestIds = $bookingRequests->pluck('request_id')->filter()->unique();
        $orders = ServiceRequest::with(['category', 'subCategory', 'address', 'user'])
            ->whereIn('id', $serviceRequestIds)
            ->get();
            // return $orders;

        return view('providers.viewProviderDetail', compact(
            'provider',
            'totalBookings',
            'acceptedOrders',
            'pendingOrders',
            'cancelBookings',
            'completedBookings',
            'serviceRequestStatuses',
            'orders',
        ));
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
            DB::beginTransaction();

            $provider = Provider::findOrFail($id);
            $providerName = $provider->full_name;

            // Delete all related records in correct order
            DB::table('deposits')->where('provider_id', $id)->delete();
            DB::table('ratings')->where('provider_id', $id)->delete();
            DB::table('provider_request_seens')->where('provider_id', $id)->delete();
            DB::table('previouses')->where('provider_id', $id)->delete();
            DB::table('wallets')->where('provider_id', $id)->delete();
            DB::table('booking_requests')->where('provider_id', $id)->delete();

            // // Delete pivot table records

            // // Delete files
            // if ($provider->profile_image) {
            //     Storage::disk('public')->delete('profiles/' . $provider->profile_image);
            // }
            // if ($provider->id_front) {
            //     Storage::disk('public')->delete('documents/' . $provider->id_front);
            // }
            // if ($provider->id_back) {
            //     Storage::disk('public')->delete('documents/' . $provider->id_back);
            // }

            // Delete provider
            $provider->delete(); // or forceDelete()

            DB::commit();

            $response = [
                'success' => true,
                'message' => "Provider \"{$providerName}\" and all associated data have been permanently deleted."
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            $response = [
                'success' => false,
                'message' => 'Failed to delete provider: ' . $e->getMessage()
            ];

            if (!(request()->wantsJson() || request()->ajax())) {
                return redirect()->back()->with('error', $response['message']);
            }

            return response()->json($response, 500);
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($response);
        }

        return redirect()->back()->with('success', $response['message']);
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

    /*
    |--------------------------------------------------------------------------
    | Preview Methods (JSON Response)
    |--------------------------------------------------------------------------
    */

    /**
     * Preview users data (returns JSON for preview modal)
     */
    public function previewUsers(Request $request)
    {
        try {
            $query = User::withCount('referrals'); // Add referrals count

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

            // Apply date range filter
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Use paginate (50 records per page)
            $users = $query->orderBy('created_at', 'desc')->paginate(50);

            // Format the data for display
            $formattedUsers = $users->map(function ($user, $index) use ($users) {
                $serialNo = ($users->currentPage() - 1) * $users->perPage() + $index + 1;

                return [
                    'Sr. No' => $serialNo,
                    'User Name' => $user->name ?? 'N/A',
                    'Image' => $user->image ?? null,
                    'Phone' => $user->phone ?? 'N/A',
                    'Referral Code' => $user->referral_code ?? 'N/A',
                    'Direct Referrals' => $user->referrals_count ?? 0,
                    'Status' => ucfirst($user->status ?? 'N/A'),
                    'Registered Date' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A',
                ];
            });

            return view('user.preview', [
                'users' => $users,
                'previewData' => $formattedUsers
            ]);
        } catch (\Throwable $e) {
            Log::error('User preview failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Preview failed: ' . $e->getMessage());
        }
    }

    /**
     * Preview providers data (returns JSON for preview modal)
     */
    public function previewProviders(Request $request)
    {
        $this->validateExportDateRange($request);

        try {
            $query = Provider::query();

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply date range filter
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Limit results for preview
            $providers = $query->orderBy('created_at', 'desc')->limit(50)->get();

            // Format data for preview
            $previewData = $providers->map(function ($provider, $index) {
                return [
                    'Sr. No' => $index + 1,
                    'Provider ID' => $provider->id,
                    'Name' => $provider->full_name ?? 'N/A',
                    'Phone' => $provider->phone ?? 'N/A',
                    'Email' => $provider->email ?? 'N/A',
                    'Status' => ucfirst($provider->status ?? 'N/A'),
                    'Registered Date' => $provider->created_at ? $provider->created_at->format('Y-m-d') : 'N/A',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $previewData,
                'total' => $providers->count(),
                'message' => 'Preview showing first 50 records'
            ]);
        } catch (\Throwable $e) {
            Log::error('Provider preview failed', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to create date filter for preview
     */
    protected function createDateFilterForPreview($filters)
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
}
