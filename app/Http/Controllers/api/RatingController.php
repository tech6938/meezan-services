<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    // rating
    public function rating(Request $request)
    {
        try {
            $user_id = Auth::id();
            $request->validate([
                'provider_id' => 'nullable|integer|exists:providers,id',
                'shopkeeper_id' => 'nullable|integer|exists:shop_keepers,id',
                'rating' => 'required|numeric|min:0|max:5',
                'review' => 'required|string',
            ]);
            $data = Rating::create([
                'provider_id' => $request->provider_id ?? null,
                'shopkeeper_id' => $request->shopkeeper_id ?? null,
                'review' => $request->review,
                'rating' => round((float)$request->rating, 1),
                'user_id' => $user_id,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Rating Added Successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
