<?php

namespace App\Http\Controllers;
use App\Models\WaterSentiment; 

use Illuminate\Http\Request;

class OfficerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch water sentiments assigned to the logged-in officer
        $waterSentiments = WaterSentiment::where('assigned_to', auth()->id())  // Use 'Assigned_to' column here
                                        ->orderBy('timestamp', 'desc')
                                        ->get();

        // Pass the waterSentiments to the view
        return view('officer.index', compact('waterSentiments'));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
