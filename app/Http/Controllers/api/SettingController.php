<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // Available settings keys
    protected $settingKeys = [
        // App & Social Settings
        'app_url',
        'whatsapp',
        'website_url',
        'tiktok_url',
        'instagram_url',
        'youtube_url',
        'facebook_url',

        // Video Tutorials
        'customer_video_tutorial_url',
        'provider_video_tutorial_url',

        // App Status Controls
        'appIsOn',
        'userAppIsOn',
        'providerAppIsOn',

        // Legal Pages - Partner
        'partner_agreement',
        'privacy_policy_partner',
        'terms&Conditions_partner',

        // Legal Pages - Customer
        'privacy_policy_customer',
        'terms&Conditions_customer',

        // Information Pages
        'about_us',
        'contact_us',
    ];
    /**
     * Get app URL setting
     */
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

    /**
     * Get all app settings
     */
    public function getApiSettings()
    {
        $settings = Setting::first();

        $response = [];
        foreach ($this->settingKeys as $key) {
            $response[$key] = $settings ? $settings->$key : null;
        }
        $response['appIsOn'] = $settings ? (bool)$settings->appIsOn : false;
        $response['userAppIsOn'] = $settings ? (bool)$settings->userAppIsOn : true;
        $response['providerAppIsOn'] = $settings ? (bool)$settings->providerAppIsOn : true;

        return response()->json([
            'status' => true,
            'message' => 'Settings retrieved successfully',
            'data' => $response
        ]);
    }
}
