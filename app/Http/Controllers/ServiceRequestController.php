<?php

namespace App\Http\Controllers;

use App\Exports\RequestsExport;
use App\Exports\RequestsMultiSheetExport;
use App\Models\ServiceRequest;
use App\Models\BookingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ServiceRequestController extends Controller
{
    /**
     * All Requests - Show all service requests with their status
     */
    public function allRequest(Request $request)
    {
        $query = ServiceRequest::with('user', 'bookingRequests')->orderBy('created_at', 'desc');
        $data = $this->applyDateRangeFilter($query, $request)->get();

        // Get status counts for badges
        $statusCounts = $this->getStatusCounts($request);

        $result = $this->formatResults($data);

        return view('serviceRequest.allRequest', compact('result', 'statusCounts'));
    }

    /**
     * Get status counts for the badges
     */
    private function getStatusCounts($request)
    {
        $baseQuery = ServiceRequest::query();

        if ($request->has('start_date') && $request->start_date) {
            $baseQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $baseQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $allRequests = $baseQuery->with('bookingRequests')->get();

        $pendingOrders = 0;
        $acceptOrders = 0;
        $assignedOrders = 0;
        $cancelledOrders = 0;
        $pendingBookings = 0;
        $completedOrders = 0;

        foreach ($allRequests as $req) {
            $booking = $this->getGoverningBooking($req);
            $displayStatus = $this->resolveDisplayStatus($req->status, $booking);

            switch ($displayStatus) {
                case 'Cancelled':
                    $cancelledOrders++;
                    break;
                case 'Completed':
                    $completedOrders++;
                    break;
                case 'Accept Order':
                    $acceptOrders++;
                    break;
                case 'Assigned':
                    $assignedOrders++;
                    break;
                case 'Pending Booking':
                    $pendingBookings++;
                    break;
                case 'Pending Order':
                    $pendingOrders++;
                    break;
            }
        }

        return [
            'pending_orders' => $pendingOrders,
            'accept_orders' => $acceptOrders,
            'assigned_orders' => $assignedOrders,
            'cancelled_orders' => $cancelledOrders,
            'pending_bookings' => $pendingBookings,
            'completed_orders' => $completedOrders,
            'total' => $allRequests->count(),
        ];
    }

    /**
     * Filter by Pending Orders (no booking, or booking not in any recognized accept-state)
     */
    public function pendingOrders(Request $request)
    {
        $data = ServiceRequest::with('user', 'bookingRequests')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        $data = $this->applyDateRangeFilter($data, $request)->get();
        $data = $data->filter(function ($req) {
            $booking = $this->getGoverningBooking($req);
            return $this->resolveDisplayStatus($req->status, $booking) === 'Pending Order';
        })->values();

        $statusCounts = $this->getStatusCounts($request);
        $result = $this->formatResults($data);

        return view('serviceRequest.orders', compact('result', 'statusCounts'))->with('type', 'pending_orders');
    }

    /**
     * Filter by Accept Order (req_status = accept, assigned = 0, goto = 0)
     */
    public function acceptedOrders(Request $request)
    {
        $data = ServiceRequest::with('user', 'bookingRequests')
            ->where('status', 'pending')
            ->whereHas('bookingRequests', function ($q) {
                $q->where('req_status', 'accept')
                    ->where('assigned', 0)
                    ->where('goto', 0);
            })
            ->orderBy('created_at', 'desc');

        $data = $this->applyDateRangeFilter($data, $request)->get();
        $statusCounts = $this->getStatusCounts($request);
        $result = $this->formatResults($data);

        return view('serviceRequest.orders', compact('result', 'statusCounts'))->with('type', 'accept_orders');
    }

    /**
     * Filter by Assigned (req_status = accept, assigned = 1, goto = 1)
     */
    public function assignedOrders(Request $request)
    {
        $data = ServiceRequest::with('user', 'bookingRequests')
            ->where('status', 'pending')
            ->whereHas('bookingRequests', function ($q) {
                $q->where('req_status', 'accept')
                    ->where('assigned', 1)
                    ->where('goto', 1);
            })
            ->orderBy('created_at', 'desc');

        $data = $this->applyDateRangeFilter($data, $request)->get();
        $statusCounts = $this->getStatusCounts($request);
        $result = $this->formatResults($data);

        return view('serviceRequest.orders', compact('result', 'statusCounts'))->with('type', 'assigned_orders');
    }

    /**
     * Filter by Pending Bookings (req_status = accept, assigned = 1, goto = 2)
     */
    public function pendingBookings(Request $request)
    {
        $data = ServiceRequest::with('user', 'bookingRequests')
            ->where('status', 'pending')
            ->whereHas('bookingRequests', function ($q) {
                $q->where('req_status', 'accept')
                    ->where('assigned', 1)
                    ->where('goto', 2);
            })
            ->orderBy('created_at', 'desc');

        $data = $this->applyDateRangeFilter($data, $request)->get();
        $statusCounts = $this->getStatusCounts($request);
        $result = $this->formatResults($data);

        return view('serviceRequest.orders', compact('result', 'statusCounts'))->with('type', 'pending_bookings');
    }

    /**
     * Filter by Cancelled Orders (status = cancel)
     */
    public function cancelledOrders(Request $request)
    {
        $data = ServiceRequest::with('user', 'bookingRequests')
            ->where('status', 'cancel')
            ->orderBy('created_at', 'desc');

        $data = $this->applyDateRangeFilter($data, $request)->get();
        $statusCounts = $this->getStatusCounts($request);
        $result = $this->formatResults($data);

        return view('serviceRequest.orders', compact('result', 'statusCounts'))->with('type', 'cancelled_orders');
    }

    /**
     * Filter by Completed Orders (status = complete)
     */
    public function completedOrders(Request $request)
    {
        $data = ServiceRequest::with('user', 'bookingRequests')
            ->where('status', 'complete')
            ->orderBy('created_at', 'desc');

        $data = $this->applyDateRangeFilter($data, $request)->get();
        $statusCounts = $this->getStatusCounts($request);
        $result = $this->formatResults($data);

        return view('serviceRequest.orders', compact('result', 'statusCounts'))->with('type', 'completed_orders');
    }

    /**
     * Format results for display - single source of truth via resolveDisplayStatus()
     */
    private function formatResults($data)
    {
        return $data->map(function ($request) {
            $fileUrls = $request->file_url;
            if (is_string($fileUrls)) {
                $fileUrls = [$fileUrls];
            } elseif (!is_array($fileUrls)) {
                $fileUrls = [];
            }

            $serviceStatus = $request->status;
            $booking = $this->getGoverningBooking($request);

            $displayStatus = $this->resolveDisplayStatus($serviceStatus, $booking);

            return [
                'id' => $request->id,
                'desc' => $request->desc,
                'lang' => $request->lang,
                'lat' => $request->lat,
                'status' => $displayStatus,
                'service_status' => $serviceStatus,
                'user_name' => $request->user ? $request->user->name : 'N/A',
                'file_urls' => $fileUrls,
                'created_at' => $request->created_at->format('Y-m-d H:i:s'),
                'has_booking' => $booking ? true : false,
                'req_status' => $booking->req_status ?? null,
                'assigned' => $booking->assigned ?? 0,
                'goto' => $booking->goto ?? 0,
            ];
        });
    }

    public function statusUpdates(Request $request)
    {
        if ($request->has('provider_id')) {
            // Find the ServiceRequest by provider_id
            $data = ServiceRequest::find($request->provider_id);

            // Check if the ServiceRequest exists
            if ($data) {
                // Update the status of the ServiceRequest
                $data->update([
                    'status' => $request->status,
                ]);

                // Redirect back with a success message
                return redirect()->back()->with('success', 'Status Updated');
            } else {
                // Return an error message if the provider_id doesn't exist
                return redirect()->back()->with('error', 'Provider not found');
            }
        } else {
            // Handle the case where provider_id is missing
            return redirect()->back()->with('error', 'Provider ID is required');
        }
    }

    public function getAcceptedProviders($id)
    {
        try {
            // Load the service request with booking requests and their providers
            $serviceRequest = ServiceRequest::with([
                'bookingRequests' => function ($query) {
                    $query->with('provider')->orderBy('created_at', 'desc');
                },
                'user',
                'category',
                'subCategory'
            ])->findOrFail($id);

            // Get all booking requests for this service request
            $allBookings = $serviceRequest->bookingRequests;

            // 1. Get the current booking state (the one that is accepted/assigned)
            // This will be the booking with assigned = 1 (accepted, in_progress, complete, cancel)
            $currentBooking = $allBookings->filter(function ($booking) {
                return $booking->assigned == 1;
            })->first();

            // 2. Get all bidded providers - everyone who placed a bid on this request.
            // This must always show every bidder, regardless of order status
            // (pending/complete/cancel) or any later change to req_status on their
            // booking row. The only booking excluded here is the current/active one,
            // since that provider is already shown in the "Current Booking State" section.
            $biddedBookings = $allBookings->reject(function ($booking) use ($currentBooking) {
                return $currentBooking && $booking->id === $currentBooking->id;
            });

            // Format current booking provider
            $currentProvider = null;
            if ($currentBooking) {
                $provider = $currentBooking->provider;

                // Determine the status display name
                $statusDisplay = 'Accepted';
                if ($currentBooking->status == 'in_progress') {
                    $statusDisplay = 'In Progress';
                } elseif ($currentBooking->status == 'complete_booking' || $currentBooking->req_status == 'complete') {
                    $statusDisplay = 'Completed';
                } elseif ($currentBooking->status == 'cancel' || $currentBooking->req_status == 'cancel') {
                    $statusDisplay = 'Cancelled';
                }

                $currentProvider = [
                    'id' => $provider->id ?? null,
                    'name' => $provider->full_name ?? $provider->name ?? 'N/A',
                    'email' => $provider->email ?? 'N/A',
                    'phone' => $provider->phone ?? 'N/A',
                    'status' => $statusDisplay,
                    'status_raw' => $currentBooking->status ?? 'N/A',
                    'req_status' => $currentBooking->req_status ?? 'N/A',
                    'goto' => $currentBooking->goto ?? 'N/A',
                    'assigned' => $currentBooking->assigned ?? 0,
                    'order_no' => $currentBooking->order_no ?? 'N/A',
                    'price' => $currentBooking->price ?? 0,
                    'created_at' => $currentBooking->created_at ? $currentBooking->created_at->format('Y-m-d H:i:s') : 'N/A',
                    'image' => $provider->profile_image_url ?? $provider->image_url ?? asset('assets/img/avatar/avatar-1.png')
                ];
            }

            // Format bidded providers
            $biddedProviders = $biddedBookings->map(function ($booking) {
                $provider = $booking->provider;
                return [
                    'id' => $provider->id ?? null,
                    'name' => $provider->full_name ?? $provider->name ?? 'N/A',
                    'email' => $provider->email ?? 'N/A',
                    'phone' => $provider->phone ?? 'N/A',
                    'status' => 'Bidded',
                    'req_status' => $booking->req_status ?? 'N/A',
                    'goto' => $booking->goto ?? 'N/A',
                    'assigned' => $booking->assigned ?? 0,
                    'order_no' => $booking->order_no ?? 'N/A',
                    'price' => $booking->price ?? 0,
                    'bidded_at' => $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : 'N/A',
                    'image' => $provider->profile_image_url ?? $provider->image_url ?? asset('assets/img/avatar/avatar-1.png')
                ];
            });

            // Debug: Log the counts
            Log::info('Request #' . $id . ' - Total Bookings: ' . $allBookings->count());
            Log::info('Request #' . $id . ' - Current Booking: ' . ($currentBooking ? 'Yes' : 'No'));
            Log::info('Request #' . $id . ' - Bidded: ' . $biddedBookings->count());

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'service_request_id' => $serviceRequest->id,
                    'service_desc' => $serviceRequest->desc,
                    'user_name' => $serviceRequest->user->name ?? 'N/A',
                    'category' => $serviceRequest->category->name ?? 'N/A',
                    'sub_category' => $serviceRequest->subCategory->name ?? 'N/A',
                    'current_provider' => $currentProvider,
                    'bidded_providers' => $biddedProviders,
                    'total_bidded' => $biddedProviders->count()
                ]);
            }

            return view('serviceRequest.accepted-providers', compact(
                'serviceRequest',
                'currentProvider',
                'biddedProviders'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading accepted providers: ' . $e->getMessage(), [
                'request_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error loading providers: ' . $e->getMessage());
        }
    }
    /**
     * Preview service requests before export
     */
    public function previewRequests(Request $request)
    {
        try {
            $query = ServiceRequest::with([
                'user',
                'category',
                'subCategory',
                'address',
                'bookingRequests'
            ]);

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('desc', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
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
            $requests = $query->orderBy('created_at', 'desc')->limit(50)->get();

            // Format data for preview
            $previewData = $requests->map(function ($request, $index) {
                // Get saved address
                $savedAddress = 'N/A';
                if ($request->address) {
                    $addressParts = [];
                    if ($request->address->address) $addressParts[] = $request->address->address;
                    if ($request->address->area) $addressParts[] = $request->address->area;
                    if ($request->address->city) $addressParts[] = $request->address->city;
                    $savedAddress = implode(', ', $addressParts);
                }

                // Get media files
                $mediaFiles = 'N/A';
                if ($request->file && is_array($request->file) && count($request->file) > 0) {
                    $mediaFiles = implode("\n", $request->file);
                }

                // Map status for display
                $statusMap = [
                    'pending' => 'Pending',
                    'accept' => 'Accept',
                    'accepted' => 'Accepted',
                    'complete' => 'Complete',
                    'completed' => 'Completed',
                    'cancel' => 'Cancel',
                    'cancelled' => 'Cancelled',
                    'rejected' => 'Rejected',
                ];

                return [
                    'Sr. No' => $index + 1,
                    'Order ID' => $request->id,
                    'User Name' => $request->user->name ?? 'N/A',
                    'User Phone' => $request->user->phone ?? 'N/A',
                    'Category' => $request->category->name ?? 'N/A',
                    'Sub Category' => $request->subCategory->name ?? 'N/A',
                    'Description' => $request->desc ?? 'N/A',
                    'Live Latitude' => $request->lat ?? 'N/A',
                    'Live Longitude' => $request->lang ?? 'N/A',
                    'Saved Address' => $savedAddress,
                    'Media Files' => $mediaFiles,
                    'Total Bids' => $request->bookingRequests->count(),
                    'Status' => $statusMap[$request->status] ?? ucfirst($request->status),
                    'Created At' => $request->created_at ? $request->created_at->format('Y-m-d H:i:s') : 'N/A',
                ];
            });

            return view('serviceRequest.preview', [
                'previewTitle' => 'Service Requests Data Preview',
                'data' => $previewData,
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Preview failed: ' . $e->getMessage());
        }
    }

    /**
     * Export service requests to Excel
     */
    public function exportRequests(Request $request)
    {
        $this->validateExportDateRange($request);

        return Excel::download(
            RequestsExport::fromRequest($request),
            'requests_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    /**
     * Export service requests with multi-sheet (Summary + Bids)
     */
    public function exportRequestsMultiSheet(Request $request)
    {
        try {
            return Excel::download(
                RequestsMultiSheetExport::fromRequest($request),
                'requests_complete_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
            );
        } catch (\Throwable $e) {
            Log::error('Requests export failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            abort(500, 'Requests export failed: ' . $e->getMessage());
        }
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

    /**
     * Generic orders view that can handle all status types
     * This is used by pendingOrders, acceptedOrders, assignedOrders, cancelledOrders, pendingBookings, completedOrders
     */
    public function ordersView(Request $request, $type = null)
    {
        $query = ServiceRequest::with('user', 'bookingRequests')->orderBy('created_at', 'desc');

        // Get status counts for badges
        $statusCounts = $this->getStatusCounts($request);

        $filterInPhp = false;

        // Apply filters based on type
        switch ($type) {
            case 'pending_orders':
                $query->where('status', 'pending');
                $filterInPhp = true; // "everything else" is easiest to resolve via resolveDisplayStatus()
                $pageTitle = 'Pending Orders';
                break;

            case 'accept_orders':
                $query->where('status', 'pending')
                    ->whereHas('bookingRequests', function ($q) {
                        $q->where('req_status', 'accept')
                            ->where('assigned', 0)
                            ->where('goto', 0);
                    });
                $pageTitle = 'Accept Orders';
                break;

            case 'assigned_orders':
                $query->where('status', 'pending')
                    ->whereHas('bookingRequests', function ($q) {
                        $q->where('req_status', 'accept')
                            ->where('assigned', 1)
                            ->where('goto', 1);
                    });
                $pageTitle = 'Assigned Orders';
                break;

            case 'pending_bookings':
                $query->where('status', 'pending')
                    ->whereHas('bookingRequests', function ($q) {
                        $q->where('req_status', 'accept')
                            ->where('assigned', 1)
                            ->where('goto', 2);
                    });
                $pageTitle = 'Pending Bookings';
                break;

            case 'cancelled_orders':
                $query->where('status', 'cancel');
                $pageTitle = 'Cancelled Orders';
                break;

            case 'completed_orders':
                $query->where('status', 'complete');
                $pageTitle = 'Completed Orders';
                break;

            default:
                $pageTitle = 'All Orders';
                break;
        }

        // Apply date range filter
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $data = $query->get();

        if ($filterInPhp) {
            $data = $data->filter(function ($req) {
                $booking = $this->getGoverningBooking($req);
                return $this->resolveDisplayStatus($req->status, $booking) === 'Pending Order';
            })->values();
        }

        // Format results
        $result = $this->formatResults($data);

        return view('serviceRequest.orders', compact('result', 'statusCounts', 'pageTitle', 'type'));
    }

    /**
     * Picks the correct booking_requests row that governs this service
     * request's display status, when multiple providers have bid.
     *
     * Priority:
     *  1. A row with assigned = 1 (the real current state: Assigned / Pending Booking)
     *  2. A row with req_status = accept, assigned = 0 (Accept Order state)
     *  3. Fallback: most recently created row (for raw display only)
     */
    private function getGoverningBooking($serviceRequest)
    {
        $bookings = $serviceRequest->relationLoaded('bookingRequests')
            ? $serviceRequest->bookingRequests
            : $serviceRequest->bookingRequests()->get();

        if ($bookings->isEmpty()) {
            return null;
        }

        $booking = $bookings->firstWhere('assigned', 1);
        if ($booking) {
            return $booking;
        }

        $booking = $bookings->first(function ($b) {
            return $b->req_status == 'accept' && $b->assigned == 0;
        });
        if ($booking) {
            return $booking;
        }

        return $bookings->sortByDesc('created_at')->first();
    }

    /**
     * Single source of truth for resolving a request's display status.
     * Rules:
     *  - service_requests.status = cancel   -> Cancelled
     *  - service_requests.status = complete -> Completed
     *  - pending, booking req_status=accept, assigned=0, goto=0 -> Accept Order
     *  - pending, booking req_status=accept, assigned=1, goto=1 -> Assigned
     *  - pending, booking req_status=accept, assigned=1, goto=2 -> Pending Booking
     *  - anything else while pending (no booking, or not in a recognized accept-state) -> Pending Order
     */
    private function resolveDisplayStatus($serviceStatus, $booking)
    {
        if ($serviceStatus == 'cancel') {
            return 'Cancelled';
        }

        if ($serviceStatus == 'complete') {
            return 'Completed';
        }

        if ($serviceStatus == 'pending' && $booking) {
            $reqStatus = $booking->req_status ?? null;
            $assigned  = $booking->assigned ?? 0;
            $goto      = $booking->goto ?? 0;

            if ($reqStatus == 'accept') {
                if ($assigned == 0 && $goto == 0) {
                    return 'Accept Order';
                }

                if ($assigned == 1 && $goto == 1) {
                    return 'Assigned';
                }

                if ($assigned == 1 && $goto == 2) {
                    return 'Pending Booking';
                }
            }
        }

        return 'Pending Order';
    }
}
