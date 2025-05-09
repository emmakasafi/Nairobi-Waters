<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\CustomerLogoutController;
use App\Http\Controllers\Auth\OfficerLoginController;
use App\Http\Controllers\Auth\HodLoginController;
use App\Http\Controllers\WaterSentimentController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\NairobiLocationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HODController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
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

// Officer Login Routes
Route::prefix('officer')->name('officer.')->group(function () {
    Route::get('login', [OfficerLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [OfficerLoginController::class, 'login']);
});



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
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

// Water Sentiment Routes
Route::get('/water_sentiments', [WaterSentimentController::class, 'index'])->name('water_sentiments');
Route::get('/water_sentiments/{id}', [WaterSentimentController::class, 'show'])->name('water_sentiments.show');
Route::get('/water_sentiments/{id}/edit', [WaterSentimentController::class, 'edit'])->name('water_sentiments.edit');
Route::post('/water_sentiments/{id}', [WaterSentimentController::class, 'update'])->name('water_sentiments.update');
Route::delete('/water_sentiments/{id}', [WaterSentimentController::class, 'destroy'])->name('water_sentiments.destroy');
Route::get('/search', [WaterSentimentController::class, 'search'])->name('search');
Route::get('/water_sentiments/data', [WaterSentimentController::class, 'dataTable'])->name('water_sentiments.data');
Route::post('/water-sentiments/{id}/assign', [WaterSentimentController::class, 'assign'])->name('water_sentiments.assign');


// Nairobi Location Routes
Route::get('/get-subcounties', [NairobiLocationController::class, 'getSubcounties']);
Route::get('/get-wards/{subcounty}', [NairobiLocationController::class, 'getWards']);

Route::resource('departments', DepartmentController::class);

// Admin Routes (Authenticated and Role-based)
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('admin.users.show');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/receive-sms', [SMSController::class, 'receive']);
});

// Route group for authenticated HODs
Route::middleware(['auth', 'is_hod'])->group(function () {
    // Route to view the HOD dashboard (listing unassigned complaints)
    Route::get('/hod', [HODController::class, 'index'])->name('hod.index');

    // Route to assign a complaint to an officer
    Route::post('/hod/assign/{complaintId}', [HODController::class, 'assign'])->name('hod.assign');
});

// Route to show the HOD login form
Route::get('hod/login', [HodLoginController::class, 'showLoginForm'])->name('hod.loginForm');

// Route to handle the HOD login request
Route::post('hod/login', [HodLoginController::class, 'login'])->name('hod.login');

// Route to handle the HOD logout
Route::post('hod/logout', [HodLoginController::class, 'logout'])->name('hod.logout');


// For Officer
Route::middleware(['auth', 'role:officer'])->prefix('officer')->name('officer.')->group(function () {
    Route::resource('officer', OfficerController::class)->names([
        'index' => 'index', // This ensures 'officer.index' points to the 'index' method
    ]);
    Route::post('/officer/update-status/{complaint}', [OfficerController::class, 'updateStatus'])->name('officer.updateStatus');
});

Route::resource('departments', DepartmentController::class);

require __DIR__.'/auth.php';
