<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;

class ServiceRequestController extends Controller
{

    public function allRequest()
    {
        $data = ServiceRequest::with('user')->get();

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
    public function pendingRequest()
    {
        // Fetch all pending requests and eager load the user relationship
        $data = ServiceRequest::where('status', 'pending')->with('user')->get();

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
    public function approvedRequest()
    {
        // Fetch all approved requests and eager load the user relationship
        $data = ServiceRequest::where('status', 'approved')->with('user')->get();

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
}
