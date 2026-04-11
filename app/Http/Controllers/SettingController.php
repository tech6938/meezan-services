<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    public function appUrl()
    {
        $data = Setting::all();
        return view('settings.app_url', compact('data'));
    }

    public function appUrlStore(Request $request)
    {
        $request->validate([
            'whatsapp' => 'nullable',
            'app_url' => 'nullable',
            'privacy_policy' => 'nullable',
        ]);

        Setting::updateOrCreate(
            ['id' => Setting::first()->id ?? null],
            [
                'whatsapp' => $request->whatsapp,
                'app_url' => $request->app_url,
                'privacy_policy' => $request->privacy_policy,
            ]
        );

        return redirect()->back()->with('success', 'Settings saved successfully');
    }

    public function appUrlDestroy(string $id)
    {
        $item = Setting::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Data Deleted Successfully');
    }

    public function appIsOn(Request $request)
    {
        try {
            $request->validate([
                'appIsOn' => 'required|in:0,1',
            ]);

            $setting = Setting::first();

            if (!$setting) {
                $setting = Setting::create([
                    'appIsOn' => $request->appIsOn,  // Use the correct column name
                ]);
            } else {
                $setting->appIsOn = $request->appIsOn;  // Use the correct column name
                $setting->save();
            }

            return response()->json([
                'status' => true,
                'message' => $request->appIsOn == 1 ? 'App has been turned ON' : 'App has been turned OFF',
                'appIsOn' => $request->appIsOn
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
                'message' => 'Failed to update app status: ' . $e->getMessage()
            ], 500);
        }
    }
}
