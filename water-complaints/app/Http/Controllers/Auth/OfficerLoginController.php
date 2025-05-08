<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Officer; // Add this if you have an Officer model

class OfficerLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.officer-login');
    }

    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials, $request->filled('remember'))) {
        // Redirect to officer dashboard
        return redirect()->route('officer.index');

    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
}

}
