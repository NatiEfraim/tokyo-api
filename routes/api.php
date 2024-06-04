<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
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

        //? search invetories records 
        Route::get('/search-records', 'searchRecords');

        //? fetch records by type_id fileds
        Route::get('/fetch-by-type', 'fetchByType');

        //? sort by sku based on type_id
        Route::get('/fetch-by-sku', 'fetchBySku');

        

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

        //?search distributions records based on one query
        Route::get('/search-by-query', 'getRecordsByQuery');

        //? fillter distributions records based on one or more fileds
        Route::get('/search-by-filter', 'getRecordsByFilter');

        //? search records by order_number.
        Route::get('/search-by-order', 'getRecordsByOrder');

        //? fetch based on only order_number fileds - and group_by (given query is optional)
        Route::get('/fetch-records-by-order', 'fetchDistributionsRecordsByOrderNumber');

        //? route for quartermaster - fetch all distributions records - only records apporved and invetories
        Route::get('/fetch-approved', 'fetchApprovedDistribution')->middleware(['role:admin|quartermaster']);

        //?sort & fetch by quering
        Route::get('/sort', 'sortByQuery');


        Route::get('/{id?}', 'getRecordById');
        //? fetch quartermaster associated to the records 
        Route::get('/fetch-quartermaster/{id?}', 'fetchQuartermaster');

        //? route for liran alocate items
        Route::post('/allocation', 'allocationRecords')->middleware(['role:admin']);
        
        //? route for to make order on item  route for user
        Route::post('/', 'store')->middleware(['role:admin|user']);
        
        //?route for quartermaster - to sign for collected or back to liran
        Route::put('/changed-status', 'changeStatus')->middleware(['role:admin|quartermaster']);


        Route::delete('/mass-destroy', 'massDestroy')->middleware(['role:admin']);

        Route::delete('/{id?}', 'destroy')->middleware(['role:admin']);
    });

Route::controller(DepartmentController::class)
    ->prefix('departments')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store')->middleware(['role:admin|user']);
        Route::delete('/mass-destroy', 'massDestroy')->middleware(['role:admin']);
        Route::delete('/{id?}', 'destroy')->middleware(['role:admin']);
    });

Route::controller(EmployeeTypeController::class)
    ->prefix('employee-types')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/', 'index');
    });
    
Route::controller(ClientController::class)
    ->prefix('clients')
    ->middleware(['auth:api'])
    ->group(function () {
        //?fetch all clients id & name
        Route::get('/', 'index');
    });

Route::controller(UserController::class)
    ->prefix('users')
    ->middleware(['auth:api'])
    ->group(function () {

        //? fetch all users records with associated roles.
        Route::get('/', 'index')->middleware(['role:admin|quartermaster']);

        //? search users records - based on pn or name.
        Route::get('/search', 'searchUser')->middleware(['role:admin|quartermaster']);

        //? fetch all roles 
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