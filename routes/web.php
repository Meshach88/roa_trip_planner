<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DestinationController;

// CRUD routes for destinations
Route::get('/destinations', [DestinationController::class, 'index']);
Route::post('/destinations', [DestinationController::class, 'store']);
Route::delete('/destinations/{id}', [DestinationController::class, 'destroy']);

// Update the order of destinations
Route::post('/destinations/update-order', [DestinationController::class, 'updateOrder']);

// Route to calculate distance and time between destinations
Route::post('/calculate-distance-time', [DestinationController::class, 'getDistancesAndTimes']);



Route::get('/', function () {
    return view('welcome');
});
