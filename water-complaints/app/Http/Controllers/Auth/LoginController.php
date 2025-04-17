<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
            'user_type' => 'required|in:customer,admin',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->role === $request->user_type) {
                if ($request->user_type === 'admin') {
                    return redirect()->intended('/admin/dashboard');
                } elseif ($request->user_type === 'customer') {
                    return redirect()->intended('/customer/dashboard');
                }
            } else {
                Auth::logout();
                return back()->with('error', 'You do not have access to this role.');
            }
        }

        return back()->withInput($request->only('email', 'remember'))->with('error', 'Invalid credentials.');
    }
}