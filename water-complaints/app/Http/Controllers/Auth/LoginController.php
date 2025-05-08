<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validate the incoming request
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
            'user_type' => 'required|in:customer,admin,hod,officer',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check if the user's role matches the selected role
            if ($user->role === $request->user_type) {
                switch ($user->role) {
                    case 'admin':
                        return redirect()->intended('/admin/dashboard');
                    case 'customer':
                        return redirect()->intended('/customer/dashboard');
                    case 'hod':
                        return redirect()->route('hod.index');
                    case 'officer':
                        return redirect()->route('officer.index');
                }
            }

            // If role mismatch
            Auth::logout();
            return back()->with('error', 'You do not have access to this role.');
        }

        // If credentials are invalid
        return back()->withInput($request->only('email'))->with('error', 'Invalid credentials.');
    }
}
