<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyRoleController;
use App\Http\Controllers\CompanyRolePermissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::get('/access-denied', function () {
    return view('accessdenied');
})->name('accessdenied');
Route::middleware('auth')->group(function () {
    //dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //users
    Route::resource('users', UserController::class);
    Route::post('/change_company', [UserController::class, 'change_company'])->name('change_company');

    //company
    Route::resource('company', CompanyController::class);

    //company role
    Route::resource('companyrole', CompanyRoleController::class);

    //company role permission
    Route::resource('permission', CompanyRolePermissionController::class);
    Route::post('change-permission', [CompanyRolePermissionController::class, 'change_permission'])->name('role_permission.change_permission');

    //folder
    Route::resource('folder', FolderController::class);
    Route::resource('file', FileController::class);
    
    Route::get('/getfiledata', [FolderController::class, 'fileManager'])->name('filemanger.data');
    Route::post('/delete/folders', [FolderController::class, 'deleteFolder'])->name('folders.delete');
});

require __DIR__ . '/auth.php';
