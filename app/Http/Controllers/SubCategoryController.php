<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\{MainCategory, SubCategory};

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $categories = SubCategory::all();
        $mainCategories = MainCategory::all();
        return view('categories.subcategories', compact('categories', 'mainCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $request->validate([
    'name' => 'required|string|max:255',
    'urdu_name' => 'nullable|string|max:255', // <-- add this
    'cat_id' => 'required|exists:main_categories,id',
    'image' => 'required|image|max:2048'
]);


        $path = $request->file('image')->store('subcategory', 'public');

       SubCategory::create([
    'name' => $request->name,
    'urdu_name' => $request->urdu_name, // <-- add this
    'cat_id' => $request->cat_id,
    'image' => $path
]);


        return redirect()->back()->with('success', 'Sub Category added successfully.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subCategory = SubCategory::findOrFail($id);

      $request->validate([
    'name' => 'required|string|max:255',
    'urdu_name' => 'nullable|string|max:255', // <-- add this
    'cat_id' => 'required|exists:main_categories,id',
    'image' => 'nullable|image|max:2048'
]);


      $subCategory->name = $request->name;
$subCategory->urdu_name = $request->urdu_name; // <-- add this
$subCategory->cat_id = $request->cat_id;

if ($request->hasFile('image')) {
    if ($subCategory->image) {
        Storage::disk('public')->delete($subCategory->image);
    }
    $subCategory->image = $request->file('image')->store('subcategory', 'public');
}

$subCategory->save();


        return redirect()->back()->with('success', 'Sub Category updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subCategory = SubCategory::findOrFail($id);

        if ($subCategory->image) {
            Storage::disk('public')->delete($subCategory->image);
        }

        $subCategory->delete();

        return redirect()->back()->with('success', 'Sub Category deleted successfully.');
    }
}
