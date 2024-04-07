<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(InventoryController::class)
    ->prefix('inventories')->group(function () {
        Route::get('/', 'index');
        Route::get('/getinventory/{id?}', 'getRecordById');
        Route::post('/', 'store');
        Route::delete('/{id?}', 'destroy');
    });
