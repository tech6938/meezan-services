<?php

namespace App\Http\Controllers\api\Shop;

use Illuminate\Http\Request;
use App\Models\ShopServiceRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShopRequestController extends Controller
{
    /**
     * Store a new shop service request
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cat_id'     => 'required|integer|exists:main_categories,id',
                'shop_id'    => 'required|integer|exists:shops,id',
                'address_id' => 'required|integer|exists:addresses,id',
                'lang'       => 'nullable|string',
                'lat'        => 'nullable|string',
                'desc'       => 'nullable|string',
                'file'       => 'nullable|array',
                'file.*'     => 'file|max:102400',
                'status'     => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            //  Handle file upload
            $filePaths = [];
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $uploadDir = public_path('uploads');

                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $file->move($uploadDir, $fileName);
                    $filePaths[] = 'uploads/' . $fileName;
                }
            }

            // Get validated data
            $data = $validator->validated();

            $data['file'] = !empty($filePaths) ? json_encode($filePaths) : null;
            $data['user_id'] = $user->id;

            $shopServiceRequest = ShopServiceRequest::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Shop service request created successfully',
                'data' => $shopServiceRequest
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all shop service requests for authenticated user
     */
    public function index()
    {
        try {
            $user = Auth::user();

            $requests = ShopServiceRequest::where('user_id', $user->id)
                ->with([
                    'user:id,name',
                    'category:id,name',
                    'shop:id,shop_name,shop_image',
                    'address:id,name,street,city'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($requests->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'No shop service requests found',
                    'data' => []
                ], 200);
            }

            $data = $requests->map(function ($request) {
                return [
                    'id'         => $request->id,
                    'cat_name'   => optional($request->category)->name,
                    'shop_name'  => optional($request->shop)->shop_name,
                    // 'shop_image' => optional($request->shop)->shop_image
                    //     ? url('shops/' . $request->shop->shop_image)
                    //     : null,
                    'desc'       => $request->desc,
                    'status'     => $request->status,
                    // 'shopkeeper_name'  => $request->user->name,
                    'created_at' => $this->formatApiDateTime(optional($request->created_at)),
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Shop service requests retrieved successfully',
                'data' => $data
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
     * Get shop service request details
     */
    public function show($id)
    {
        try {
            $request = ShopServiceRequest::with([
                'category:id,name',
                'shop:id,shop_name,shop_image,lang,lat',
                'address:id,name,street,city,PostalCode',
                'user:id,name,image',
                'shopBookingRequest'
            ])->find($id);

            if (!$request) {
                return response()->json([
                    'status' => false,
                    'message' => 'Shop service request not found'
                ], 404);
            }

            // Build address
            $address = null;
            if ($request->address) {
                $address = collect([
                    $request->address->name,
                    $request->address->street,
                    $request->address->city,
                    $request->address->PostalCode,
                ])->filter()->implode(', ');
            }

            $data = [
                'id'         => $request->id,
                'user_id'    => $request->user_id,
                'cat_name'   => optional($request->category)->name,
                'shop_name'  => optional($request->shop)->shop_name,
                'shop_image' => optional($request->shop)->shop_image
                    ? url('shops/' . $request->shop->shop_image)
                    : null,
                'address'    => $address,
                'address_id' => $request->address_id,
                'lang'       => $request->lang,
                'lat'        => $request->lat,
                'desc'       => $request->desc,
                'file'       => $request->file,
                'file_type'  => $request->file_type,
                'status'     => $request->status,
                'created_at' => $this->formatApiDateTime(optional($request->created_at)),
            ];

            return response()->json([
                'status' => true,
                'message' => 'Shop service request details retrieved successfully',
                'data' => $data
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
