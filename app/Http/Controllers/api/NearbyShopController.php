<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;

class NearbyShopController extends Controller
{
    public function nearbyShop()
    {
        try {
            $data = Shop::with('shopkeeper:id,name')
                ->get()
                ->map(function ($shop) {
                    return [
                        'id' => $shop->id,
                        'shop_name' => $shop->shop_name,
                        'shop_image' => $shop->shop_image,
                        'lat' => $shop->lat,
                        'lang' => $shop->lang,
                        'category' => $shop->category ?? null,
                        'shopkeeper' => $shop->shopkeeper->name ?? null,
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'All Shops Are Here',
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
