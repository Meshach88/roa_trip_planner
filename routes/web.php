<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DestinationController;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

Route::get('/', function () {
    return view('welcome');
});


// CRUD routes for destinations
Route::get('/destinations', [DestinationController::class, 'index']);
Route::post('/api/destinations', [DestinationController::class, 'store']);
Route::delete('/api/destinations/{id}', [DestinationController::class, 'destroy']);

// Update the order of destinations
Route::post('/destinations/update-order', [DestinationController::class, 'updateOrder']);

// Route to calculate distance and time between destinations
Route::post('/api/calculate-distance-time', [DestinationController::class, 'getDistancesAndTimes']);



Route::get('/proxy-osrm', function (Request $request) {
    // Retrieve the coordinates from the request query parameters
    $lat1 = $request->query('lat1');
    $lng1 = $request->query('lng1');
    $lat2 = $request->query('lat2');
    $lng2 = $request->query('lng2');

    // Validate the coordinates (optional but recommended)
    if (!$lat1 || !$lng1 || !$lat2 || !$lng2) {
        return response()->json(['error' => 'Coordinates are required'], 400);
    }

    // Construct the OSRM URL dynamically using the received coordinates
    $osrmUrl = "http://router.project-osrm.org/route/v1/driving/{$lng1},{$lat1};{$lng2},{$lat2}?overview=false";

    // Use GuzzleHttp to send the request to OSRM
    $client = new Client();
    try {
        $response = $client->get($osrmUrl);
        $data = $response->getBody()->getContents();
        return response()->json(json_decode($data, true)); // Return the OSRM response
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to fetch data from OSRM'], 500);
    }
});


