<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class CustomerLogoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:customer'); // Ensure this middleware uses the 'customer' guard
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout(); // Specify the 'customer' guard for logout
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('customer.login'); // Redirect to the customer login route
    }
}