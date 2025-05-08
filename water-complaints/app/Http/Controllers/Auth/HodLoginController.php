<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User; // Ensure you are using the User model for authentication

class HodLoginController extends Controller
{
    /**
     * Show the login form for the HOD.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.hod-login');
    }

    /**
     * Handle the login request for the HOD.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validate the input
        $credentials = $request->only('email', 'password');

        // Attempt to log in the HOD using the credentials
        if (Auth::attempt($credentials)) {
            // Check if the logged-in user is an HOD
            if (Auth::user()->role == 'hod') {
                return redirect()->route('hod.index');
            }

            // If not an HOD, log out and redirect
            Auth::logout();
            return back()->withErrors(['email' => 'You are not authorized to access this page.']);
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.']);
    }

    /**
     * Log the HOD out of the application.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login'); // Or the route you use for regular users to log in
    }
}
