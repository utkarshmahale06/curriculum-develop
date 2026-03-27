<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cdc\CdcDashboardController;
use App\Http\Controllers\Cdc\CdcDepartmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// CDC routes
Route::prefix('cdc')->middleware(['auth', 'cdc'])->group(function () {
    Route::get('/dashboard', [CdcDashboardController::class, 'index'])->name('cdc.dashboard');
    Route::get('/departments', [CdcDepartmentController::class, 'index'])->name('cdc.departments.index');
    Route::get('/departments/create', [CdcDepartmentController::class, 'create'])->name('cdc.departments.create');
    Route::post('/departments/store', [CdcDepartmentController::class, 'store'])->name('cdc.departments.store');
});
