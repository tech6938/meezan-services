<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tax; // we'll create this model

class TaxController extends Controller
{
    public function index()
    {
        $tax = Tax::first(); // assume only one tax setting
        return view('tax.index', compact('tax'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
        ]);

        $tax = Tax::first() ?? new Tax();
        $tax->type = $request->type;
        $tax->amount = $request->amount;
        $tax->save();

        return redirect()->back()->with('success', 'Tax saved successfully!');
    }
}
