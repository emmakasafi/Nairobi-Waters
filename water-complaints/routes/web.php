<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\CustomerLogoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home');
})->middleware(['auth'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Login Routes
Route::get('/admin/login', function () {
    return view('auth.admin-login');
})->name('admin.login');

Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');

// Customer Login Routes
Route::get('/customer/login', function () {
    return view('auth.customer-login');
})->name('customer.login');

Route::post('/customer/login', [CustomerLoginController::class, 'login'])->name('customer.login.submit');

// Admin Dashboard Route
Route::get('/admin/dashboard', function () {
    return view('admin-dashboard');
})->middleware(['auth', 'role:admin'])->name('admin.dashboard');

// Customer Dashboard Route
Route::get('/customer/dashboard', function () {
    return view('customer-dashboard');
})->middleware(['auth', 'role:user'])->name('customer.dashboard');

// Admin Logout Route
Route::post('/admin/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('admin.logout');

/// Customer Logout Route
Route::post('/customer/logout', [CustomerLogoutController::class, 'logout'])->name('customer.logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
});

require __DIR__.'/auth.php';