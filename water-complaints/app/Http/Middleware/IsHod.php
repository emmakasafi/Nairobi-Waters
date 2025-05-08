<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsHod
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the logged-in user is an HOD
        if (Auth::check() && Auth::user()->role === 'hod') {
            return $next($request);
        }

        // Redirect to home if not an HOD
        return redirect('/');
    }
}
