<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\EmployeeTypeController;
use App\Http\Controllers\ExportController;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\UserController;



// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');




Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class,'logout'])
->middleware(['auth:api']);



Route::get("/user", [UserController::class, "user"])->middleware(['auth:api']);


Route::controller(InventoryController::class)
    ->prefix('inventories')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/sku-records', 'getSkuRecords');
        Route::get('/search-records', 'searchRecords');
    Route::get('/{id?}', 'getRecordById');

        Route::put('/{id?}', 'update');
        Route::post('/', 'store');
        Route::delete('/mass-destroy', 'massDestroy');
        Route::delete('/{id?}', 'destroy');
    });




Route::controller(DistributionController::class)
    ->prefix('distributions')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/search-by-query', 'getRecordsByQuery');
        Route::get('/{id?}', 'getRecordById');
        Route::put('/changed-status/{id?}', 'changeStatus');
        Route::put('/{id?}', 'update');
        Route::post('/', 'store');
        Route::delete('/mass-destroy', 'massDestroy');
        Route::delete('/{id?}', 'destroy');
    });

Route::controller(DepartmentController::class)
    ->prefix('departments')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::delete('/mass-destroy', 'massDestroy');
        Route::delete('/{id?}', 'destroy');
    });

Route::controller(EmployeeTypeController::class)
    ->prefix('employee-types')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/', 'index');
    });

Route::controller(UserController::class)
    ->prefix('users')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/search', 'searchUser');

        Route::post('/', 'store');
        // Route::put('/{id?}', 'update');
        Route::delete('/mass-destroy', 'massDestroy');
        Route::delete('/{id?}', 'destroy');
    });

///Export tables to Excel.
///these routes the user must has permission_name='admin'
Route::controller(ExportController::class)
    ->prefix('export')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/inventories', 'exportInventories');
        Route::get('/inventories-email', 'sendInventoriesByEmail');
        Route::get('/users', 'exportUsers');
        Route::get('/users-email', 'sendUsersByEmail');
        Route::get('/distributions', 'exportDistributions');
        Route::get('/distributions-email', 'sendDistributionsByEmail');
    });
