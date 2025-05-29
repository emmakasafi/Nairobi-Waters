<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\Auth\CustomerLogoutController;
use App\Http\Controllers\Auth\OfficerLoginController;
use App\Http\Controllers\Auth\HodLoginController;
use App\Http\Controllers\WaterSentimentController;
use App\Http\Controllers\Officer\OfficerComplaintController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\NairobiLocationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HODController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Welcome Route
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Registration Routes
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Login Routes
    Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminLoginController::class, 'login'])->name('login.submit');

    // Admin Authenticated Routes
    Route::middleware(['auth:admin', 'role:admin'])->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', function () {
            Auth::guard('admin')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('admin.login');
        })->name('logout');

        // User Management
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Export Routes
        Route::get('export/csv', [AdminDashboardController::class, 'exportCsv'])->name('export.csv');
        Route::get('export/excel', [AdminDashboardController::class, 'exportExcel'])->name('export.excel');
        Route::get('export/pdf', [AdminDashboardController::class, 'exportPdf'])->name('export.pdf');

        // Wards by Subcounty
        Route::get('wards-by-subcounty', [AdminDashboardController::class, 'getWardsBySubcounty'])->name('wards.by.subcounty');

        // SMS
        Route::post('receive-sms', [SMSController::class, 'receive'])->name('receive-sms');
    });
});

// Customer Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Customer Login Routes
    Route::get('login', function () {
        return view('auth.customer-login');
    })->name('login');
    Route::post('login', [CustomerLoginController::class, 'login'])->name('login.submit');

    // Customer Authenticated Routes
    Route::middleware(['auth:customer', 'role:user'])->group(function () {
        Route::get('dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [CustomerLogoutController::class, 'logout'])->name('logout');

        // Notification Routes
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/respond', [NotificationController::class, 'respond'])->name('notifications.respond');
        Route::get('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::get('/notifications/count', [NotificationController::class, 'getNotificationCount'])->name('notifications.count');
    });
});

// Officer Routes
Route::prefix('officer')->name('officer.')->group(function () {
    // Officer Login Routes
    Route::get('login', [OfficerLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [OfficerLoginController::class, 'login'])->name('login.submit');

    // Officer Authenticated Routes
    Route::middleware(['auth', 'role:officer'])->group(function () {
        Route::get('dashboard', [OfficerComplaintController::class, 'index'])->name('officer.index');
        Route::get('complaints/{complaint}', [OfficerComplaintController::class, 'show'])->name('officer.show');
        Route::post('complaints/{complaint}/update-status', [OfficerComplaintController::class, 'updateComplaintStatus'])->name('officer.updateStatus');
        Route::get('complaints/{complaint}/status-options', [OfficerComplaintController::class, 'getAvailableStatusOptions'])->name('officer.getStatusOptions');

        // Officer Resource Routes
        Route::resource('officer', OfficerController::class)->names([
            'index' => 'officer.index',
            'create' => 'officer.create',
            'store' => 'officer.store',
            'show' => 'officer.show',
            'edit' => 'officer.edit',
            'update' => 'officer.update',
            'destroy' => 'officer.destroy',
        ]);
    });
});

// HOD Routes
Route::prefix('hod')->name('hod.')->group(function () {
    // HOD Login Routes
    Route::get('login', [HodLoginController::class, 'showLoginForm'])->name('loginForm');
    Route::post('login', [HodLoginController::class, 'login'])->name('login');
    
    // HOD Authenticated Routes
    Route::middleware(['auth', 'is_hod'])->group(function () {
        Route::get('/', [HODController::class, 'index'])->name('index');
        Route::post('assign/{complaintId}', [HODController::class, 'assign'])->name('assign');
        Route::post('logout', [HodLoginController::class, 'logout'])->name('logout');
    });
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Profile Routes
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Complaint Routes
    Route::get('complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('complaints', [ComplaintController::class, 'store'])->name('complaints.store');

    // Dashboard Route
    Route::get('dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');
});

// Water Sentiment Routes
Route::prefix('water_sentiments')->name('water_sentiments.')->group(function () {
    Route::get('/', [WaterSentimentController::class, 'index'])->name('index');
    Route::get('{id}', [WaterSentimentController::class, 'show'])->name('show');
    Route::get('{id}/edit', [WaterSentimentController::class, 'edit'])->name('edit');
    Route::post('{id}', [WaterSentimentController::class, 'update'])->name('update');
    Route::delete('{id}', [WaterSentimentController::class, 'destroy'])->name('destroy');
    Route::post('{id}/assign', [WaterSentimentController::class, 'assign'])->name('assign');
    Route::get('data', [WaterSentimentController::class, 'dataTable'])->name('data');
});

// Search Route
Route::get('search', [WaterSentimentController::class, 'search'])->name('search');

// Nairobi Location Routes
Route::get('get-subcounties', [NairobiLocationController::class, 'getSubcounties'])->name('get-subcounties');
Route::get('get-wards/{subcounty}', [NairobiLocationController::class, 'getWards'])->name('get-wards');

// Department Routes
Route::resource('departments', DepartmentController::class);

require __DIR__.'/auth.php';