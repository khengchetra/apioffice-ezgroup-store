<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\CheckPermission\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\Organization_Structure\DepartmentController;
use App\Http\Controllers\Api\Organization_Structure\PositionController;
use App\Http\Controllers\Api\Products\Attribute\AttributeController;
use App\Http\Controllers\Api\Products\CategoryController;
use App\Http\Controllers\Api\Products\ProductController\ProductController;
use App\Http\Controllers\Api\Request\ReqestCategoryController;
use App\Http\Controllers\Api\service\BranchController;
use App\Http\Controllers\Api\UserManagment\RoleController;
use App\Http\Controllers\Api\UserManagment\UserController;
use Illuminate\Support\Facades\Log;

Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User APIs
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/check-username', [UserController::class, 'checkUsername'])->name('users.checkUsername');
    Route::post('/check-email', [UserController::class, 'checkEmail'])->name('users.checkEmail');
    Route::post('/verify-password', [UserController::class, 'verifyPassword'])->name('users.verifyPassword');
    Route::put('/change-password', [UserController::class, 'changePassword'])->name('users.changePassword');
    Route::post('/users/{id}/profile-image', [UserController::class, 'updateProfileImage'])->name('users.updateProfileImage');
});

// Role APIs
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    
});

// branch
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/branchs', [BranchController::class, 'index'])->name('index');
    Route::get('/branchs/{id}', [BranchController::class, 'show'])->name('show');
    Route::post('/branchs', [BranchController::class, 'store'])->name('store');
    Route::put('/branchs/{id}', [BranchController::class, 'update'])->name('update');
    Route::delete('/branchs/{id}', [BranchController::class, 'destroy'])->name('destroy');
});


//Organization Structure
Route::middleware('auth:sanctum')->group(function () {
    // Department
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/{id}', [DepartmentController::class, 'show'])->name('departments.show');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    // position
    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
    Route::get('/positions/{id}', [PositionController::class, 'show'])->name('positions.show');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::put('/positions/{id}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{id}', [PositionController::class, 'destroy'])->name('positions.destroy');
});

// Permission Check API
Route::middleware('auth:sanctum')->post('/check-permission', [PermissionController::class, 'checkPermission'])->name('check.permission');

// Combined Master Data API
Route::middleware('auth:sanctum')->get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('index');
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('show');
    Route::post('/categories', [CategoryController::class, 'store'])->name('store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('destroy');
});


Route::middleware('auth:sanctum')->group(function () {
    // Original routes
    Route::get('/reqest-categories', [ReqestCategoryController::class, 'index'])->name('index');
    Route::get('/reqest-categories/{id}', [ReqestCategoryController::class, 'show'])->name('show');
    Route::post('/reqest-categories', [ReqestCategoryController::class, 'store'])->name('store');
    Route::put('/reqest-categories/{id}', [ReqestCategoryController::class, 'update'])->name('update');
    Route::delete('/reqest-categories/{id}', [ReqestCategoryController::class, 'destroy'])->name('destroy');  
    // New routes
    Route::get('/reqest-categories-active/list', [ReqestCategoryController::class, 'getActiveCategories'])->name('getActiveCategories');
    Route::patch('/reqest-categories/{id}/toggle-active', [ReqestCategoryController::class, 'updateActiveStatus'])->name('updateActiveStatus');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
    Route::get('/attributes/{id}', [AttributeController::class, 'show'])->name('attributes.show');
    Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
    Route::put('/attributes/{id}', [AttributeController::class, 'update'])->name('attributes.update');
    Route::delete('/attributes/{id}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
});

// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/attribute/select', [ProductController::class, 'Attribute']);
    Route::get('/attribute-values/select', [ProductController::class, 'AttributeValue']);
    Route::patch('/products/{id}/toggle-active', [ProductController::class, 'updateActiveStatus'])->name('products.updateActiveStatus');
    Route::post('/products/check-code', [ProductController::class, 'checkProductCode'])->name('products.checkProductCode');
    Route::get('/mobile/products', [ProductController::class, 'randomProducts'])->name('products.mobile.index');
    Route::get('/mobile/products/search', [ProductController::class, 'searchProducts'])->name('products.mobile.search');
});