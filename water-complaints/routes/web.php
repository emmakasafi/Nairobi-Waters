<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\CustomerLogoutController;
use App\Http\Controllers\WaterSentimentController;
use App\Http\Controllers\AdminDashboardController; // Add this line
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Home Route
Route::get('/', function () {
    return view('welcome');
});

// Home Route (Authenticated)
Route::get('/home', function () {
    return view('home');
})->middleware(['auth'])->name('home');

// Dashboard Route (Authenticated and Verified)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Login Routes
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');

// Admin Dashboard Route
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->middleware(['auth:admin', 'role:admin'])->name('admin.dashboard');

// Admin Logout Route
Route::post('/admin/logout', function (Request $request) {
    Auth::guard('admin')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/admin/login');
})->middleware('auth:admin')->name('admin.logout');

// Customer Login Routes
Route::get('/customer/login', function () {
    return view('auth.customer-login');
})->name('customer.login');

Route::post('/customer/login', [CustomerLoginController::class, 'login'])->name('customer.login.submit');

// Customer Dashboard Route
Route::get('/customer/dashboard', function () {
    return view('customer-dashboard');
})->middleware(['auth:customer', 'role:user'])->name('customer.dashboard');

// Customer Logout Route
Route::post('/customer/logout', [CustomerLogoutController::class, 'logout'])->name('customer.logout');

// Profile Routes (Authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Dashboard and Complaint Routes (Authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
});

// Registration Routes
Route::get('/register', [Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [Auth\RegisterController::class, 'register'])->name('register.submit');

// Water Sentiment Routes
Route::get('/water_sentiments', [WaterSentimentController::class, 'index'])->name('water_sentiments');
Route::get('/water_sentiments/{id}', [WaterSentimentController::class, 'show'])->name('water_sentiments.show');
Route::get('/water_sentiments/{id}/edit', [WaterSentimentController::class, 'edit'])->name('water_sentiments.edit');
Route::post('/water_sentiments/{id}', [WaterSentimentController::class, 'update'])->name('water_sentiments.update');
Route::delete('/water_sentiments/{id}', [WaterSentimentController::class, 'destroy'])->name('water_sentiments.destroy');
Route::get('/search', [WaterSentimentController::class, 'search'])->name('search');
Route::get('/water_sentiments/data', [WaterSentimentController::class, 'dataTable'])->name('water_sentiments.data');

require __DIR__.'/auth.php';