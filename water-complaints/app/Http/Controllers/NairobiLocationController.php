<?php

// NairobiLocationController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NairobiLocation;

class NairobiLocationController extends Controller
{
    // Fetch distinct subcounties
    public function getSubcounties()
    {
        $subcounties = NairobiLocation::select('subcounty')->distinct()->get();
        return response()->json($subcounties);
    }

    // Fetch wards based on subcounty
    public function getWards($subcounty)
    {
        $wards = NairobiLocation::where('subcounty', $subcounty)->pluck('ward');
        return response()->json($wards);
    }
}
