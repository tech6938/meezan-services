<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\{MainCategory, SubCategory};

class MainCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MainCategory::all();
        return view('categories.maincategories', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'urdu_name' => 'required',
            'image' => 'required',
        ]);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('MainCategory', 'public');
        }
        $data = MainCategory::create([
            'name' => $request->name,
            'urdu_name' => $request->urdu_name,
            'image' => $path,
        ]);
        if ($data) {
            return redirect()->back()->with('success', 'Category Added Successfuly');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MainCategory $main_category)
    {
        $providers = $main_category->providers()->paginate(10);

        return view('categories.viewdetails', compact('main_category', 'providers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MainCategory $main_category)
    {
        $request->validate([
            'name' => 'required',
            'urdu_name' => 'required',
            'image' => 'nullable|image',
        ]);
        $main_category->name = $request->name;
        $main_category->urdu_name = $request->urdu_name;
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($main_category->image && Storage::disk('public')->exists($main_category->image)) {
                Storage::disk('public')->delete($main_category->image);
            }
            // Store new image
            $image = $request->file('image');
            $path = $image->store('MainCategory', 'public');
            $main_category->image = $path;
        }
        $main_category->save();
        return redirect()->back()->with('success', 'Category updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MainCategory $main_category)
    {

        // Check if any subcategories are assigned
        if ($main_category->subCategories()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete! This category has subcategories assigned.');
        }
        // Delete image from storage
        if ($main_category->image && Storage::disk('public')->exists($main_category->image)) {
            Storage::disk('public')->delete($main_category->image);
        }

        $main_category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
