<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return 'test';
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// user register
Route::post('/user/register', [\App\Http\Controllers\Api\AuthController::class, 'userRegister']);

// restaurant register
Route::post('/restaurant/register', [\App\Http\Controllers\Api\AuthController::class, 'restaurantRegister']);

// driver register
Route::post('/driver/register', [\App\Http\Controllers\Api\AuthController::class, 'driverRegister']);

// login
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

// logout 

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    
    // update latlong
    Route::put('/user/update-latlong', [\App\Http\Controllers\Api\AuthController::class, 'updateLatLong']);

    // get restaurant
    Route::get('/restaurant', [\App\Http\Controllers\Api\AuthController::class, 'getAllRestaurant']);

    // product resource
    Route::apiResource('/product', \App\Http\Controllers\Api\ProductController::class);

    // order
    Route::post('/order', [\App\Http\Controllers\Api\OrderController::class, 'store']);
    //get order by user id
    Route::get('/order/user', [\App\Http\Controllers\Api\OrderController::class, 'orderHistory']);
    // get order by restaurant id
    Route::get('/order/restaurant', [\App\Http\Controllers\Api\OrderController::class, 'getOrderByStatus']);
    //get order by driver id
    Route::get('/order/driver', [\App\Http\Controllers\Api\OrderController::class, 'getOrdersByStatusDriver']);
    
    // update order status for restaurant
    Route::put('/order/restaurant/update-status/{id}', [\App\Http\Controllers\Api\OrderController::class, 'updateOrderStatus']);
    // update order status for driver
    Route::put('/order/driver/update-status/{id}', [\App\Http\Controllers\Api\OrderController::class, 'updateStatusForDriver']);
    Route::put('/order/user/update-status/{id}', [\App\Http\Controllers\Api\OrderController::class, 'updatePurchaseStatus']);


});
