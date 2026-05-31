<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ServiceRequest;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RequestsExport;
class ServiceRequestController extends Controller
{

    public function allRequest(Request $request)
    {
        $data = $this->applyDateRangeFilter(ServiceRequest::with('user'), $request)->get();

        $result = $data->map(function ($request) {
            // Get file URLs (could be string or array)
            $fileUrls = $request->file_url;

            // Ensure it's always an array for consistent handling
            if (is_string($fileUrls)) {
                $fileUrls = [$fileUrls];
            } elseif (!is_array($fileUrls)) {
                $fileUrls = [];
            }

            return [
                'id' => $request->id,
                'desc' => $request->desc,
                'lang' => $request->lang,
                'lat' => $request->lat,
                'status' => $request->status,
                'user_name' => $request->user ? $request->user->name : 'N/A',
                'file_urls' => $fileUrls, // Changed to array
                'created_at' => $request->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return view('serviceRequest.allRequest', compact('result'));
    }

    // pending
    public function pendingRequest(Request $request)
    {
        // Fetch all pending requests and eager load the user relationship
        $data = $this->applyDateRangeFilter(ServiceRequest::where('status', 'pending')->with('user'), $request)->get();

        // Map the data to include user name and ensure consistent file URL handling
        $result = $data->map(function ($request) {
            // Get the user's name from the related user record
            $userName = $request->user ? $request->user->name : 'N/A';

            return [
                'id' => $request->id,
                'lang' => $request->lang,
                'lat' => $request->lat,
                'status' => $request->status,
                'user_name' => $userName, // Add user name here
            ];
        });

        // Return the view with the result
        return view('serviceRequest.pending', compact('result'));
    }

    // approved
    public function approvedRequest(Request $request)
    {
        // Fetch all approved requests and eager load the user relationship
        $data = $this->applyDateRangeFilter(ServiceRequest::where('status', 'approved')->with('user'), $request)->get();

        // Map the data to include user name and ensure consistent file URL handling
        $result = $data->map(function ($request) {
            // Get the user's name from the related user record
            $userName = $request->user ? $request->user->name : 'N/A';

            return [
                'id' => $request->id,
                'lang' => $request->lang,
                'lat' => $request->lat,
                'status' => $request->status,
                'user_name' => $userName, // Add user name here
            ];
        });

        // Return the view with the result
        return view('serviceRequest.approved', compact('result'));
    }

    // status  update
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
            $serviceRequest = ServiceRequest::with(['acceptedProviders' => function ($query) {
                $query->withPivot('status', 'created_at', 'order_no', 'price');
            }])->findOrFail($id);

            $providers = $serviceRequest->acceptedProviders->map(function ($provider) use ($serviceRequest) {
                return [
                    'id' => $provider->id,
                    'name' => $provider->name ?? $provider->full_name ?? 'N/A',
                    'email' => $provider->email ?? 'N/A',
                    'phone' => $provider->phone ?? 'N/A',
                    'status' => $provider->pivot->status,
                    'order_no' => $provider->pivot->order_no,
                    'price' => $provider->pivot->price,
                    'accepted_at' => $provider->pivot->created_at->format('Y-m-d H:i:s'),
                    'image' => $provider->image_url ?? $provider->profile_image ?? asset('assets/img/avatar/avatar-1.png')
                ];
            });

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'service_request_id' => $serviceRequest->id,
                    'service_desc' => $serviceRequest->desc,
                    'providers' => $providers
                ]);
            }

            return view('serviceRequest.accepted-providers', compact('serviceRequest', 'providers'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error loading providers');
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

}
