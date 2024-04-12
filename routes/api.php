<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\EmployeeTypeController;
use App\Http\Controllers\ExportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(InventoryController::class)
    ->prefix('inventories')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{id?}', 'getRecordById');
        Route::get('/sku-records', 'getSkuRecords');
        Route::get('/search-records/{searchString?}', 'searchRecords');
        Route::put('/{id?}', 'update');
        Route::post('/', 'store');
        Route::delete('/mass-destroy', 'massDestroy');
        Route::delete('/{id?}', 'destroy');
    });

Route::controller(DistributionController::class)
    ->prefix('distributions')
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
    ->prefix('depratments')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::delete('/mass-destroy', 'massDestroy');
        Route::delete('/{id?}', 'destroy');
    });

Route::controller(EmployeeTypeController::class)
    ->prefix('employee-types')
    ->group(function () {
        Route::get('/', 'index');
    });

Route::controller(UserController::class)
    ->prefix('users')
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
    ->group(function () {
        Route::get('/inventories', 'exportInventories');
        Route::get('/inventories-email', 'sendMissionEmail');
    });
