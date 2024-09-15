<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    // Fetch all destinations
    public function index()
    {
        return response()->json(Destination::orderBy('order')->get());
    }

    // Store a new destination
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $destination = new Destination([
            'name' => $validated['name'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'order' => Destination::count(), // Assign sequential order
        ]);

        $destination->save();

        return response()->json($destination, 201);
    }

    // Update the order of the destinations
    public function updateOrder(Request $request)
    {
        $orderData = $request->validate([
            'destinations' => 'required|array',
        ]);

        foreach ($orderData['destinations'] as $index => $destinationId) {
            $destination = Destination::find($destinationId);
            $destination->order = $index;
            $destination->save();
        }

        return response()->json(['message' => 'Order updated successfully']);
    }

    // Delete a destination
    public function destroy($id)
    {
        $destination = Destination::findOrFail($id);
        $destination->delete();

        return response()->json(['message' => 'Destination deleted successfully']);
    }

    // Fetch distance and duration between destinations (Google API integration)
    public function getDistancesAndTimes(Request $request)
    {
        $validated = $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
            'waypoints' => 'array',
        ]);

        $origin = $validated['origin'];
        $destination = $validated['destination'];
        $waypoints = isset($validated['waypoints']) ? implode('|', $validated['waypoints']) : '';

        // Call the Google Maps Distance Matrix API
        $apiKey = config('services.google_maps.api_key');
        $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$origin}&destination={$destination}&waypoints={$waypoints}&key={$apiKey}";

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        // Return the routes' distance and duration details
        return response()->json([
            'distance' => $data['routes'][0]['legs'][0]['distance']['text'],
            'duration' => $data['routes'][0]['legs'][0]['duration']['text'],
        ]);
    }
}
