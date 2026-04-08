<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function appUrl()
    {
        try {
            $data = Setting::first();

            return response()->json([
                'status' => true,
                'message' => 'Settings retrieved successfully',
                'data' => $data ? [
                    'id' => $data->id,
                    'whatsapp' => $data->whatsapp,
                    'app_url' => $data->app_url,
                    'privacy_policy' => $data->privacy_policy
                ] : null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
