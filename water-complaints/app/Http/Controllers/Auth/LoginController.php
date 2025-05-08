<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validate the login form inputs
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'user_type' => 'required|in:admin,customer,officer,hod', // Include 'hod' here
        ]);

        // Attempt to log in with the credentials
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check if the logged-in user's role matches the requested user type
            if ($user->role === $request->user_type) {
                switch ($user->role) {
                    case 'admin':
                        return redirect()->intended('/admin/dashboard');
                    case 'customer':
                        return redirect()->intended('/customer/dashboard');
                    case 'officer':
                        return redirect()->route('officer.index');
                    case 'hod': // Add the HOD case here
                        return redirect()->route('hod.index'); // Make sure 'hod.index' route is defined
                }
            }

            // If the roles do not match, log out and return an error
            Auth::logout();
            return back()->with('error', 'You do not have access to this role.');
        }

        // If login credentials are invalid
        return back()->with('error', 'Invalid login credentials.');
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }
}
