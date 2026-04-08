<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{MainCategory, SubCategory, User};
use Google\Cloud\Storage\Connection\Rest;

class CategoryController extends Controller
{
    // for main categories
    public  function MainCategories()
    {

        try {
            $data = MainCategory::all();
            return response()->json([
                'status' => true,
                'message' => "Main categories retrieved successfully!",
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }


    // Get subcategories by main category id
    public function SubCategoriesByMain($id)
    {
        try {
            $data = SubCategory::where('cat_id', $id)->get();
            if ($data->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'No subcategories found for this category.',
                    'data' => [],
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Sub categories retrieved successfully!',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }


    // all categories
    public function allCategories()
    {
        try {
            $data = MainCategory::with('subCategories')->get();

            return response()->json([
                'status' => true,
                'message' => 'Categories with subcategories retrieved successfully!',
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
