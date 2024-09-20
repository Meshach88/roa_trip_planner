<template>
  <div class="trip-planner max-w-lg mx-auto p-6 bg-white rounded-lg shadow-md">

    <h1 class="text-blue-950 font-bold text-center mb-5 text-xl">Road Trip Planner</h1>

    <!-- Add Destination Form -->
    <div>
      <form @submit.prevent="addDestination" class="space-y-4">
        <input type="text" v-model="newDestination.name" placeholder="Destination name" required
          class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <input type="number" v-model="newDestination.latitude" step="0.001" placeholder="Latitude" required
          class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <input type="number" v-model="newDestination.longitude" step="0.001" placeholder="Longitude" required
          class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <button type="submit"
          class="w-full text-white bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-md text-sm font-semibold">Add
          Destination</button>
      </form>
    </div>


    <!-- Destination List -->
    <draggable v-model="destinations" tag="ul" @end="recalculateJourney" :itemKey="element => element.id">
      <template #item="{ element: destination, index }">
        <ul class="divide-y divide-gray-200">
          <li class="py-4">
            <div class="flex justify-between items-center">
              <p class="font-bold">
                {{ destination.name }}
                <!-- (Lat: {{ destination.latitude }}, Lng: {{ destination.longitude
                }}) -->
              </p>
              <button @click="removeDestination(index)" class="text-sm text-red-400 hover:text-red-800">Remove</button>
            </div>
          </li>
        </ul>
      </template>
    </draggable>

    <!-- Map Component -->
    <h2 class="text-center font-bold text-base mt-6 text-blue-500">Map</h2>
    <div class="h-full w-full">
      <MapView :destinations="destinations" ref="mapView" @add-destination="addDestinationFromMap" class="my-3" />
    </div>
    <!-- Recalculate Journey -->
    <button @click="recalculateJourney"
      class="mt-6 w-full text-white bg-green-500 hover:bg-green-600 px-4 py-2 rounded-md font-semibold">Recalculate
      Journey</button>

    <!-- Journey Summary -->
    <div v-if="totalDistance || totalTime" class="mt-6">
      <h2 class="text-lg font-semibold">Journey Summary</h2>
      <p>Total Distance: {{ totalDistance }}</p>
      <p>Total Time: {{ totalTime }}</p>
    </div>
  </div>
</template>

<script>
import MapView from './MapView.vue';
import axios from 'axios';
import draggable from 'vuedraggable';
import { ref } from 'vue';


export default {
  name: 'TripPlanner',
  components: {
    draggable,
    MapView,
  },
  data() {
    return {
      newDestination: {
        name: '',
        latitude: null,
        longitude: null,
      },
      destinations: [], // List of added destinations
      totalDistance: '',
      totalTime: '',
    };
    // const destination = ref(destination);
  },
  methods: {
    addDestination() {
      axios.post('/api/destinations', this.newDestination)
        .then(response => {
          this.destinations.push(response.data);
          this.saveToLocalStorage();
          this.resetForm();
        })
        .catch(error => {
          console.error('Error adding destination:', error);
        });
    },
    addDestinationFromMap(destination) {
      // destination.id = Date.now();  //Assign a unique ID based on the current timestamp
      this.destinations.push(destination);
      this.saveToLocalStorage();
    },
    removeDestination(index) {
      const destinationId = this.destinations[index].id;
      if (destinationId) {
        axios.delete(`/api/destinations/${destinationId}`)
          .then(() => {
            this.destinations.splice(index, 1); // Remove destination from the array
            this.saveToLocalStorage(); // Update local storage after removal
          })
          .catch(error => {
            console.error('Error removing destination:', error);
          });
      } else {
        // If the destination has no id (for those added from map click), just remove it
        this.destinations.splice(index, 1); // Remove destination from the array
        this.saveToLocalStorage(); // Update local storage after removal
      }
    },
    recalculateJourney() {
      console.log(this.destinations);

      if (this.destinations.length < 2) {
        console.error('Need at least two destinations to calculate journey.');
        return;
      }

      const start = this.destinations[0];
      const end = this.destinations[this.destinations.length - 1];

      // Waypoints are the destinations between the first and last
      const waypoints = this.destinations.slice(1, -1).map(destination => `${destination.longitude},${destination.latitude}`);

      // Construct the URL for OSRM API, including waypoints
      const osrmUrl = `http://router.project-osrm.org/route/v1/driving/${start.longitude},${start.latitude};${waypoints.join(';')};${end.longitude},${end.latitude}?overview=true&geometries=geojson`;

      // Fetch the route from OSRM
      axios.get(osrmUrl)
        .then(response => {
          const route = response.data.routes[0];

          const routeCoordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]); // Swap lat/lng order for Leaflet

          // Draw the route on the map
          L.polyline(routeCoordinates, { color: 'blue' }).addTo(this.map);
          const totalDistance = (route.distance / 1000).toFixed(2); // in kilometers
          const totalTime = (route.duration / 3600).toFixed(2); // in hours

          // Update the distance and time in your component's data
          this.totalDistance = totalDistance + ' km';
          this.totalTime = totalTime + ' hours';
        })
        .catch(error => {
          console.error('Error fetching route:', error);
        });
    },

    saveToLocalStorage() {
      localStorage.setItem('destinations', JSON.stringify(this.destinations));
    },
    resetForm() {
      this.newDestination.name = '';
      this.newDestination.latitude = null;
      this.newDestination.longitude = null;
    }
  },
  mounted() {
    const savedDestinations = localStorage.getItem('destinations');
    if (savedDestinations) {
      this.destinations = JSON.parse(savedDestinations);
    }
  }
};
</script>

<style scoped>
.trip-planner {
  font-family: 'Arial', sans-serif;
}

#map {
  height: 100%
}
</style>
