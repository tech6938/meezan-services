<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Address;

class AddressController extends Controller
{
    // get api for address 
    public function address()
    {
        try {
            $userId = Auth::id();
            // return $data;
            $data = Address::where('user_id', $userId)->get();
            return response()->json([
                'status' => true,
                'Message' => 'All Addresses Retrieved Successfuly',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'Message' => $e->getMessage(),
            ]);
        }
    }
    // post api for address 
    public function storeAddress(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'street' => 'required',
                'city' => 'required',
                'PostalCode' => 'required',
            ]);
            $userId = Auth::id();
            $data = Address::create([
                'name' => $request->name,
                'street' => $request->street,
                'city' => $request->city,
                'PostalCode' => $request->PostalCode,
                'user_id' => $userId,
            ]);
            return response()->json([
                'status' => true,
                'Message' => 'Address Added Successfuly',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'Message' => $e->getMessage(),
            ]);
        }
    }
    // for address update 
    public function updateAddress(Request $request, $id)
    {
        try {
            $data = Address::find($id);
            if ($data) {
                $data->name = $request->name  ??  $data->name;
                $data->city = $request->city ?? $data->city;
                $data->street = $request->street ?? $data->street;
                $data->PostalCode = $request->PostalCode ??  $data->PostalCode;
                $data->save();
                return response()->json([
                    'status' => 'true',
                    'message' => 'Data Updated Successfuly',
                    'data' => $data,
                ]);
            } else {
                return response()->json([
                    'status' => 'true',
                    'message' => 'No Recored Found For This'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => $e->getMessage(),
            ]);
        }
    }
    // for delete addressDelete 
    public function addressDelete($id)
    {
        try {
            $data = Address::find($id);
            if ($data) {
              $data->delete();
                return response()->json([
                    'status' => 'true',
                    'message' => 'Data Deleted Successfuly',
                ]);
            } else {
                return response()->json([
                    'status' => 'true',
                    'message' => 'No Recored Found For This'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
