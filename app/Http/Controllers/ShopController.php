<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopKeeper;
use App\Models\Shop;

class ShopController extends Controller
{
    // shopkeepers
    public function shopkeepers()
    {
        $data = ShopKeeper::all();
        return view('shop.shopkeeper', compact('data'));
    }

    public function statusUpdate(Request $request)
    {
        // Validate request
        $request->validate([
            'shopKeeper_id' => 'required|exists:shop_keepers,id',
            'status' => 'required'
        ]);

        // Find shopKeeper
        $shopKeeper = ShopKeeper::find($request->shopKeeper_id);

        // Update status
        $shopKeeper->status = $request->status;
        $shopKeeper->save();

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    // shop
    public function shops($id)
    {
        $data = Shop::with('shopkeeper')->where('shopkeeper_id', $id)->get();
        return view('shop.shop', compact('data'));
    }

    public function destroy($id)
    {
        $shopKeeper = ShopKeeper::with('shops')->findOrFail($id);

        if ($shopKeeper->shops()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete shopkeeper with active shops.');
        }

        $shopKeeper->delete();

        return redirect()->route('shopkeepers')->with('success', 'Shopkeeper deleted successfully.');
    }
}
