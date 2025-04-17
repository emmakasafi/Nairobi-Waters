<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->filled('remember'))) {
            $user = Auth::guard('admin')->user();
            Log::info('Admin login attempt successful for user: ' . $user->email);
            if ($user->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            } else {
                Auth::guard('admin')->logout();
                return back()->with('error', 'You do not have admin access.');
            }
        } else {
            Log::info('Admin login attempt failed for email: ' . $request->email);
            return back()->withInput($request->only('email', 'remember'))->with('error', 'Invalid credentials.');
        }
    }
}