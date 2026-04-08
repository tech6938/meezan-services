<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

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
}
