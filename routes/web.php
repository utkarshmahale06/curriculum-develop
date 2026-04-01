<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cdc\CdcDashboardController;
use App\Http\Controllers\Cdc\CdcDepartmentController;
use App\Http\Controllers\Cdc\CdcSchemeAssignmentController;
use App\Http\Controllers\Cdc\CdcUserManagementController;
use App\Http\Controllers\Department\DepartmentCourseController;
use App\Http\Controllers\Department\DepartmentDashboardController;
use App\Http\Controllers\Faculty\FacultyDashboardController;
use App\Http\Controllers\Hod\HodCourseAssignmentController;
use App\Http\Controllers\Hod\HodDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check() && auth()->user()->isDepartment()) {
        return redirect()->route('department.dashboard');
    }

    if (auth()->check() && auth()->user()->isCdc()) {
        return redirect()->route('cdc.dashboard');
    }

    if (auth()->check() && auth()->user()->isHod()) {
        return redirect()->route('hod.dashboard');
    }

    if (auth()->check() && auth()->user()->isFaculty()) {
        return redirect()->route('faculty.dashboard');
    }

    return redirect()->route('login');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/department/login', [AuthController::class, 'showDepartmentLoginForm'])->name('department.login');
    Route::post('/department/login', [AuthController::class, 'departmentLogin'])->name('department.login.submit');
    Route::get('/hod/login', [AuthController::class, 'showHodLoginForm'])->name('hod.login');
    Route::post('/hod/login', [AuthController::class, 'hodLogin'])->name('hod.login.submit');
    Route::get('/hod/register', [AuthController::class, 'showHodRegisterForm'])->name('hod.register');
    Route::get('/faculty/login', [AuthController::class, 'showFacultyLoginForm'])->name('faculty.login');
    Route::post('/faculty/login', [AuthController::class, 'facultyLogin'])->name('faculty.login.submit');
    Route::get('/faculty/register', [AuthController::class, 'showFacultyRegisterForm'])->name('faculty.register');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// CDC routes
Route::prefix('cdc')->middleware(['auth', 'cdc'])->group(function () {
    Route::get('/dashboard', [CdcDashboardController::class, 'index'])->name('cdc.dashboard');
    Route::get('/users', [CdcUserManagementController::class, 'index'])->name('cdc.users.index');
    Route::get('/users/create', [CdcUserManagementController::class, 'create'])->name('cdc.users.create');
    Route::post('/users', [CdcUserManagementController::class, 'store'])->name('cdc.users.store');
    Route::get('/departments', [CdcDepartmentController::class, 'index'])->name('cdc.departments.index');
    Route::get('/departments/create', [CdcDepartmentController::class, 'create'])->name('cdc.departments.create');
    Route::post('/departments/store', [CdcDepartmentController::class, 'store'])->name('cdc.departments.store');
    Route::get('/departments/{department}', [CdcSchemeAssignmentController::class, 'show'])->name('cdc.departments.show');
    Route::get('/departments/{department}/assign', [CdcSchemeAssignmentController::class, 'edit'])->name('cdc.departments.assign');
    Route::post('/departments/{department}/assign', [CdcSchemeAssignmentController::class, 'update'])->name('cdc.departments.assign.update');
    Route::post('/departments/{department}/approve', [CdcSchemeAssignmentController::class, 'approve'])->name('cdc.departments.approve');
    Route::post('/departments/{department}/request-revision', [CdcSchemeAssignmentController::class, 'requestRevision'])->name('cdc.departments.request-revision');
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

Route::prefix('hod')->middleware(['auth', 'hod'])->group(function () {
    Route::get('/dashboard', [HodDashboardController::class, 'index'])->name('hod.dashboard');
    Route::get('/departments/{department}/faculty-assignments', [HodCourseAssignmentController::class, 'edit'])->name('hod.faculty-assignments.edit');
    Route::post('/departments/{department}/faculty-assignments', [HodCourseAssignmentController::class, 'update'])->name('hod.faculty-assignments.update');
});

Route::prefix('faculty')->middleware(['auth', 'faculty'])->group(function () {
    Route::get('/dashboard', [FacultyDashboardController::class, 'index'])->name('faculty.dashboard');
});
