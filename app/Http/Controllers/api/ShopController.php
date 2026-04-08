<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    public function shop(Request $request)
    {
        try {
            $request->validate([
                'shop_name' => 'required|unique:shops,shop_name',
                'shop_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // validate as image
                'lang' => 'required',
                'lat' => 'required',
                'category' => 'required',
                'shopkeeper_id' => 'required',
            ]);

            // Handle file upload
            if ($request->hasFile('shop_image')) {
                $path = $request->file('shop_image')->store('shop', 'public'); // stores in storage/app/public/shop
            } else {
                $path = null;
            }

            // Create shop
            $shop = Shop::create([
                'shop_name' => $request->shop_name,
                'shop_image' => $path,
                'lang' => $request->lang,
                'lat' => $request->lat,
                'category' => $request->category,
                'shopkeeper_id' => $request->shopkeeper_id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Shop Registered Successfully',
                'data' => $shop,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    // user Shops
    public function userShops()
    {
        try {
            $shopkeeper  = auth()->guard('shopkeeper-api')->id();
            $data = Shop::where('shopkeeper_id', $shopkeeper)->get();
            return response()->json([
                'status' => true,
                'message' => 'Your Shops fetched Successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    // delete shop
    public function shopDelete($id)
    {
        try {
            $data = Shop::find($id);
            if (!$data) {
                return response()->json([
                    'status' => true,
                    'message' => 'There is No Shop',
                ]);
            }
            $delete = $data->delete();
            if ($delete)
                return response()->json([
                    'status' => true,
                    'message' => 'Your Shops Deleted Successfully',
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
