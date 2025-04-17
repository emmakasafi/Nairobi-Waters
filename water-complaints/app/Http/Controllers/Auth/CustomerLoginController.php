<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.customer-login');
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('customer')->attempt(['email' => $request->email, 'password' => $request->password], $request->filled('remember'))) {
            $user = Auth::guard('customer')->user();
            if ($user->role === 'user') { // Check the role
                return redirect('/customer/dashboard'); //redirect to the customer dashboard
            } else {
                Auth::guard('customer')->logout();
                return back()->with('error', 'You do not have customer access.');
            }
        }

        return back()->withInput($request->only('email', 'remember'))->with('error', 'Invalid credentials.');
    }
}