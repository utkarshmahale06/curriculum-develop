<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cdc\CdcDashboardController;
use App\Http\Controllers\Cdc\CdcDepartmentController;
use App\Http\Controllers\Cdc\CdcSchemeAssignmentController;
use App\Http\Controllers\Department\DepartmentCourseController;
use App\Http\Controllers\Department\DepartmentDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check() && auth()->user()->isDepartment()) {
        return redirect()->route('department.dashboard');
    }

    if (auth()->check() && auth()->user()->isCdc()) {
        return redirect()->route('cdc.dashboard');
    }

    return redirect()->route('login');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/department/login', [AuthController::class, 'showDepartmentLoginForm'])->name('department.login');
    Route::post('/department/login', [AuthController::class, 'departmentLogin'])->name('department.login.submit');
    Route::get('/department/register', [AuthController::class, 'showDepartmentRegisterForm'])->name('department.register');
    Route::post('/department/register', [AuthController::class, 'departmentRegister'])->name('department.register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// CDC routes
Route::prefix('cdc')->middleware(['auth', 'cdc'])->group(function () {
    Route::get('/dashboard', [CdcDashboardController::class, 'index'])->name('cdc.dashboard');
    Route::get('/departments', [CdcDepartmentController::class, 'index'])->name('cdc.departments.index');
    Route::get('/departments/create', [CdcDepartmentController::class, 'create'])->name('cdc.departments.create');
    Route::post('/departments/store', [CdcDepartmentController::class, 'store'])->name('cdc.departments.store');
    Route::get('/departments/{department}', [CdcSchemeAssignmentController::class, 'show'])->name('cdc.departments.show');
    Route::get('/departments/{department}/assign', [CdcSchemeAssignmentController::class, 'edit'])->name('cdc.departments.assign');
    Route::post('/departments/{department}/assign', [CdcSchemeAssignmentController::class, 'update'])->name('cdc.departments.assign.update');
    Route::get('/departments/{department}/course-codes', [CdcSchemeAssignmentController::class, 'editCourseCodes'])->name('cdc.departments.course-codes.edit');
    Route::post('/departments/{department}/course-codes', [CdcSchemeAssignmentController::class, 'updateCourseCodes'])->name('cdc.departments.course-codes.update');
});

Route::prefix('department')->middleware(['auth', 'department'])->group(function () {
    Route::get('/dashboard', [DepartmentDashboardController::class, 'index'])->name('department.dashboard');
    Route::get('/schemes/{department}/courses/view', [DepartmentCourseController::class, 'show'])->name('department.courses.show');
    Route::get('/schemes/{department}/courses', [DepartmentCourseController::class, 'edit'])->name('department.courses.edit');
    Route::post('/schemes/{department}/courses', [DepartmentCourseController::class, 'update'])->name('department.courses.update');
    Route::post('/schemes/{department}/courses/submit', [DepartmentCourseController::class, 'submitToCdc'])->name('department.courses.submit');
});
