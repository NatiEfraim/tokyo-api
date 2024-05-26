<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\EmployeeTypeController;
use App\Http\Controllers\ExportController;
// use Illuminate\Http\Request;
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



Route::get("/user", [UserController::class, "user"])->middleware(['auth:api']);


Route::controller(InventoryController::class)
    ->prefix('inventories')
    ->middleware(['auth:api'])
    ->group(function () {



        
        Route::get('/', 'index');
        
        
        //? fetch sku & id only
        Route::get('/sku-records', 'getSkuRecords');
        
        Route::get('/search-records', 'searchRecords');
        
        Route::get('/fetch-by-type', 'fetchByType');
        
        //? fetch all reports records by sku of invetory records (property sku:5487415).
        Route::get('/history', 'fetchReport')->middleware(['role:admin']);

        Route::get('/{id?}', 'getRecordById');
        



        Route::put('/{id?}', 'update')->middleware(['role:admin|quartermaster']);

        Route::post('/', 'store')->middleware(['role:admin']);

        Route::delete('/mass-destroy', 'massDestroy')->middleware(['role:admin']);

        Route::delete('/{id?}', 'destroy')->middleware(['role:admin']);
    });




Route::controller(DistributionController::class)
    ->prefix('distributions')
    ->middleware(['auth:api'])
    ->group(function () {

        Route::get('/', 'index');

        Route::get('/search-by-query', 'getRecordsByQuery');

        Route::get('/search-by-filter', 'getRecordsByFilter');

        Route::get('/search-by-order', 'getRecordsByOrder');

        Route::get('/fetch-records-by-order', 'fetchDistributionsRecordsByOrderNumber');

        //?sprt & fetch by quering
        Route::get('/sortByQuery', 'sortByQuery');


        Route::get('/{id?}', 'getRecordById');

        Route::put('/changed-status/{id?}', 'changeStatus')->middleware(['role:admin']);

        Route::put('/{id?}', 'update')->middleware(['role:admin|quartermaster']);

        Route::post('/', 'store')->middleware(['role:admin|user']);

        Route::delete('/mass-destroy', 'massDestroy')->middleware(['role:admin']);

        Route::delete('/{id?}', 'destroy')->middleware(['role:admin']);
    });

Route::controller(DepartmentController::class)
    ->prefix('departments')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store')->middleware(['role:admin']);
        Route::delete('/mass-destroy', 'massDestroy')->middleware(['role:admin']);
        Route::delete('/{id?}', 'destroy')->middleware(['role:admin']);
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

        Route::get('/', 'index')->middleware(['role:admin']);

        Route::get('/search', 'searchUser')->middleware(['role:admin']);

        Route::get('/roles', 'getRoles')->middleware(['role:admin']);


        Route::post('/', 'store')->middleware(['role:admin']);
        Route::put('/{id?}', 'update')->middleware(['role:admin']);
        
        Route::delete('/mass-destroy', 'massDestroy')->middleware(['role:admin']);
        Route::delete('/{id?}', 'destroy')->middleware(['role:admin']);
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


    Route::controller(ItemTypeController::class)
    ->prefix('item-type')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/search-record', 'searchRecords');
        Route::post('/', 'store');
        Route::put('/{id?}', 'update');
        Route::delete('/{id?}', 'destroy');

    });
