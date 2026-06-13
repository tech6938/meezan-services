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
        // App & Social Settings
        'app_url',
        'whatsapp',
        'website_url',
        'twitter_url',
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
        'referral_enabled',
        'referral_type',
        'referral_level_1',
        'referral_level_2',
        'referral_level_3',
        'referral_min_amount',
        'referral_max_amount',

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

        // Get or create the settings record
        $setting = Setting::firstOrNew(['id' => 5]);

        // If it's a new record, set default values
        if (!$setting->exists) {
            $setting->id = 5;
            $setting->app_url = null;
            $setting->whatsapp = null;
            $setting->website_url = null;
            $setting->twitter_url = null;
            $setting->instagram_url = null;
            $setting->youtube_url = null;
            $setting->facebook_url = null;
            $setting->customer_video_tutorial_url = null;
            $setting->provider_video_tutorial_url = null;
            $setting->appIsOn = 0;
            $setting->userAppIsOn = 0;
            $setting->providerAppIsOn = 0;
            $setting->referral_enabled = 0;
            $setting->referral_type = 'percentage';
            $setting->referral_level_1 = 0;
            $setting->referral_level_2 = 0;
            $setting->referral_level_3 = 0;
            $setting->referral_min_amount = 0;
            $setting->referral_max_amount = 0;
            $setting->partner_agreement = null;
            $setting->privacy_policy_partner = null;
            $setting->termsConditions_partner = null;
            $setting->privacy_policy_customer = null;
            $setting->termsConditions_customer = null;
            $setting->about_us = null;
            $setting->contact_us = null;
            $setting->save();
        }

        // Update only the specific setting key
        $setting->{$request->setting_key} = $request->setting_value;
        $setting->save();

        return redirect()->back()->with('success', ucfirst(str_replace('_', ' ', $request->setting_key)) . ' saved successfully');
    }

    /**
     * Delete setting (reset to null)
     */
    public function appUrlDestroy($key)
    {
        $setting = Setting::find(5);

        if ($setting && $setting->{$key} !== null) {
            $setting->{$key} = null;
            $setting->save();

            return response()->json([
                'status' => true,
                'message' => ucfirst(str_replace('_', ' ', $key)) . ' deleted successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Setting not found'
        ], 404);
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

    /**
     * Display privacy policy for providers
     */
    public function privacyPolicy()
    {
        return view('privacy_policy.provider');
    }

    /**
     * Display terms and conditions for providers
     */
    public function termsConditions()
    {
        return view('terms_&_conditions.provider');
    }

    /**
     * Display privacy policy for customers
     */
    public function privacyCustomer()
    {
        return view('privacy_policy.customer');
    }

    /**
     * Display terms and conditions for customers
     */
    public function termsConditionsCustomer()
    {
        return view('terms_&_conditions.customer');
    }

    /**
     * Display partner agreement
     */
    public function partnerAgreement()
    {
        return view('settings.provider_agreement');
    }

    /**
     * Display about us page
     */
    public function aboutUs()
    {
        return view('settings.about_us');
    }

    /**
     * Display contact us page
     */
    public function contactUs()
    {
        return view('settings.contact_us');
    }
}
