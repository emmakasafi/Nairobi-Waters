<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Hod; // Add this if you have a HOD model

class HodLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.hod-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('hod')->attempt($credentials, $request->remember)) {
            // Redirect to HOD's dashboard
            return redirect()->route('hod.index');
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }
}
