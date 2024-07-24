<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\EmployeeTypeController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ItemTypeController;
use App\Http\Controllers\UserController;



// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');




Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class,'logout'])
->middleware(['auth:api']);



Route::get("/user", [UserController::class, "user"])->middleware(['auth:api', 'role:admin|quartermaster|user']);


Route::controller(InventoryController::class)
    ->prefix('inventories')
    ->middleware(['auth:api'])
    ->group(function () {

        Route::get('/', 'index')->middleware(['role:admin|quartermaster|user']);

        Route::get('/sku-records', 'getSkuRecords')->middleware(['role:admin|quartermaster|user']);

        Route::get('/search-records', 'searchRecords')->middleware(['role:admin|quartermaster|user']);

        Route::get('/fetch-by-type', 'fetchByType')->middleware(['role:admin|quartermaster|user']);

        Route::get('/fetch-by-sku', 'fetchBySku')->middleware(['role:admin|quartermaster|user']);

        Route::get('/history', 'fetchReport')->middleware(['role:admin']);

        Route::get('/{id?}', 'getRecordById')->middleware(['role:admin|quartermaster|user']);

        Route::put('/{id?}', 'update')->middleware(['role:admin|quartermaster']);

        Route::post('/', 'store')->middleware(['role:admin|quartermaster']);

        Route::delete('/mass-destroy', 'massDestroy')->middleware(['role:admin']);

        Route::delete('/{id?}', 'destroy')->middleware(['role:admin']);
    });




Route::controller(DistributionController::class)
    ->prefix('distributions')
    ->middleware(['auth:api'])
    ->group(function () {

        Route::get('/', 'index')->middleware(['role:admin|quartermaster|user']);

        Route::get('/fetch-history', 'fetchRecordsByType')->middleware(['role:admin|user|quartermaster']);

        Route::get('/search-by-query', 'getRecordsByQuery')->middleware(['role:admin|quartermaster|user']);

        Route::get('/search-by-filter', 'getRecordsByFilter')->middleware(['role:admin']);

        Route::get('/search-by-order', 'getRecordsByOrder')->middleware(['role:admin|quartermaster|user']);

        Route::get('/fetch-records-by-order', 'fetchDistributionsRecordsByOrderNumber')->middleware(['role:admin|quartermaster']);

        Route::get('/fetch-approved', 'fetchApprovedDistribution')->middleware(['role:admin|quartermaster']);

        Route::get('/sort', 'sortByQuery')->middleware(['role:admin|quartermaster|user']);

        Route::get('/fetch-quartermaster/{id?}', 'fetchQuartermaster')->middleware(['role:admin']);

        Route::get('/{id?}', 'getRecordById')->middleware(['role:admin|quartermaster|user']);
        
        Route::post('/allocation', 'allocationRecords')->middleware(['role:admin']);

        Route::post('/', 'store')->middleware(['role:admin|quartermaster|user']);
        
        Route::put('/changed-status', 'changeStatus')->middleware(['role:admin|quartermaster']);

        Route::delete('/{id?}', 'destroy')->middleware(['role:admin']);
    });

Route::controller(DepartmentController::class)
    ->prefix('departments')
    ->middleware(['auth:api'])
    ->group(function () {

        Route::get('/', 'index')->middleware(['role:admin|quartermaster|user']);

        Route::post('/', 'store')->middleware(['role:admin|user']);

        Route::delete('/mass-destroy', 'massDestroy')->middleware(['role:admin']);

        Route::delete('/{id?}', 'destroy')->middleware(['role:admin']);

    });

Route::controller(EmployeeTypeController::class)
    ->prefix('employee-types')
    ->middleware(['auth:api'])
    ->group(function () {

        Route::get('/', 'index')->middleware(['role:admin|quartermaster|user']);

    });
    
Route::controller(ClientController::class)
    ->prefix('clients')
    ->middleware(['auth:api'])
    ->group(function () {

        Route::get('/', 'index')->middleware(['role:admin|quartermaster|user']);

        Route::get('/search', 'searchClients')->middleware(['role:admin|quartermaster']);
    });

Route::controller(UserController::class)
    ->prefix('users')
    ->middleware(['auth:api'])
    ->group(function () {

        Route::get('/', 'index')->middleware(['role:admin|quartermaster']);

        Route::get('/search', 'searchUser')->middleware(['role:admin|quartermaster']);

        Route::get('/roles', 'getRoles')->middleware(['role:admin']);

        Route::post('/', 'store')->middleware(['role:admin']);
        
        Route::put('/{id?}', 'update')->middleware(['role:admin']);

        Route::delete('/mass-destroy', 'massDestroy')->middleware(['role:admin']);

        Route::delete('/{id?}', 'destroy')->middleware(['role:admin']);
    });

///Export tables to Excel.
Route::controller(ExportController::class)
    ->prefix('export')
    ->middleware(['auth:api','role:admin'])
    ->group(function () {

        Route::get('/inventories', 'exportInventories')->middleware(['role:admin|quartermaster|user']);

        Route::get('/users', 'exportUsers')->middleware(['role:admin|quartermaster|user']);
        
        Route::get('/distributions', 'exportDistributions')->middleware(['role:admin|quartermaster|user']);

    });


    Route::controller(ItemTypeController::class)
    ->prefix('item-type')
    ->middleware(['auth:api'])
    ->group(function () {

        Route::get('/', 'index')->middleware(['role:admin|quartermaster|user']);

        Route::get('/search-record', 'searchRecords')->middleware(['role:admin|quartermaster|user']);

        Route::post('/', 'store')->middleware(['role:admin|quartermaster|user']);
        
        Route::put('/{id?}', 'update')->middleware(['role:admin|quartermaster|user']);

        Route::delete('/{id?}', 'destroy')->middleware(['role:admin|quartermaster|user']);

    });