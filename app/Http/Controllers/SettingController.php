<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    // Available settings keys
    protected $settingKeys = [
        'app_url',
        'privacy_policy',
        'whatsapp',
        'website_url',
        'twitter_url',
        'instagram_url',
        'youtube_url',
        'facebook_url',
        'terms_and_conditions_url',
        'customer_video_tutorial_url',
        'provider_video_tutorial_url',
    ];

    /**
     * Display all settings
     */
    public function appUrl()
    {
        $settings = Setting::first(); // Get the single settings record
        $settingKeys = $this->settingKeys;

        // If no settings exist, create an empty model
        if (!$settings) {
            $settings = new Setting();
        }

        return view('settings.app_url', compact('settings', 'settingKeys'));
    }

    /**
     * Store or update setting
     */
    public function appUrlStore(Request $request)
    {
        $request->validate([
            'setting_key' => 'required|in:' . implode(',', $this->settingKeys),
            'setting_value' => 'required|string',
        ]);

        $setting = Setting::firstOrCreate(['id' => 5]);

        // Update the setting key with the value
        $setting->update([
            $request->setting_key => $request->setting_value
        ]);

        return redirect()->back()->with('success', ucfirst(str_replace('_', ' ', $request->setting_key)) . ' saved successfully');
    }

    /**
     * Delete setting (reset to null)
     */
    public function appUrlDestroy(string $key)
    {
        if (!in_array($key, $this->settingKeys)) {
            return redirect()->back()->with('error', 'Invalid setting key');
        }

        $setting = Setting::first();
        if ($setting) {
            $setting->update([
                $key => null
            ]);
        }

        return redirect()->back()->with('success', 'Setting deleted successfully');
    }

    /**
     * Toggle User App Status
     */
    public function userAppIsOn(Request $request)
    {
        try {
            $request->validate([
                'userAppIsOn' => 'required|in:0,1',
            ]);

            $setting = Setting::first();

            if (!$setting) {
                $setting = Setting::create([
                    'userAppIsOn' => $request->userAppIsOn,
                ]);
            } else {
                $setting->userAppIsOn = $request->userAppIsOn;
                $setting->save();
            }

            return response()->json([
                'status' => true,
                'message' => $request->userAppIsOn == 1 ? 'User app has been turned ON' : 'User app has been turned OFF',
                'userAppIsOn' => $request->userAppIsOn
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update user app status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle Provider App Status
     */
    public function providerAppIsOn(Request $request)
    {
        try {
            $request->validate([
                'providerAppIsOn' => 'required|in:0,1',
            ]);

            $setting = Setting::first();

            if (!$setting) {
                $setting = Setting::create([
                    'providerAppIsOn' => $request->providerAppIsOn,
                ]);
            } else {
                $setting->providerAppIsOn = $request->providerAppIsOn;
                $setting->save();
            }

            return response()->json([
                'status' => true,
                'message' => $request->providerAppIsOn == 1 ? 'Provider app has been turned ON' : 'Provider app has been turned OFF',
                'providerAppIsOn' => $request->providerAppIsOn
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update provider app status: ' . $e->getMessage()
            ], 500);
        }
    }
}
