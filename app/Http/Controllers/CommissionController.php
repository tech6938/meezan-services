<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\MainCategory;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CommissionController extends Controller
{
    public function index()
    {
        $commissionLogCount = DB::table('commission_logs')
            ->selectRaw('COUNT(*)')
            ->whereColumn('commission_logs.sub_category_id', 'commissions.sub_category_id');

        $commissionLogSum = DB::table('commission_logs')
            ->selectRaw('COALESCE(SUM(commission_deducted), 0)')
            ->whereColumn('commission_logs.sub_category_id', 'commissions.sub_category_id');

        $commissions = Commission::with(['mainCategory', 'subCategory'])
            ->select('commissions.*')
            ->selectSub($commissionLogCount, 'total_completed_bookings')
            ->selectSub($commissionLogSum, 'total_commission_earned')
            ->paginate(10);

        $categories = MainCategory::all();

        return view('commission.index', compact('commissions', 'categories'));
    }

    public function create()
    {
        $categories = MainCategory::all();
        return view('commission.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'             => 'required|in:fixed,percentage',
            'amount'           => 'required|numeric|min:0',
            'main_category_id' => 'required|exists:main_categories,id',
            'sub_category_id'  => 'required|exists:sub_categories,id',
        ]);

        Commission::create($request->only('type', 'amount', 'main_category_id', 'sub_category_id'));

        return redirect()->route('commission.index')->with('success', 'Commission created');
    }

    public function edit($id)
    {
        $commission = Commission::findOrFail($id);
        $mainCategories = MainCategory::all(); // ← was $categories, now $mainCategories
        $subCategories = SubCategory::where('cat_id', $commission->main_category_id)->get();

        return view('commission.edit', compact('commission', 'mainCategories', 'subCategories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type'             => 'required|in:fixed,percentage',
            'amount'           => 'required|numeric|min:0',
            'main_category_id' => 'required|exists:main_categories,id',
            'sub_category_id'  => 'required|exists:sub_categories,id',
        ]);

        Commission::findOrFail($id)->update(
            $request->only('type', 'amount', 'main_category_id', 'sub_category_id')
        );

        return redirect()->route('commission.index')->with('success', 'Updated successfully');
    }

    public function destroy($id)
    {
        Commission::findOrFail($id)->delete();
        return back()->with('success', 'Deleted successfully');
    }


    public function getSubCategories($id)
    {
        $subCategories = SubCategory::where('cat_id', $id)->get();
        return response()->json($subCategories);
    }
}
