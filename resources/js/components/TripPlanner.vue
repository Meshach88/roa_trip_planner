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

    <!-- Map Component -->
    <div class="h-full w-full">
      <MapView :destinations="destinations" @add-destination="addDestinationFromMap" class="my-6" />
    </div>

    <!-- Destination List -->
    <draggable v-model="destinations" @end="recalculateJourney">
      <ul class="divide-y divide-gray-200">

        <li v-for="(destination, index) in destinations" :key="destination.id" class="py-4">
          <div class="flex justify-between items-center">
            <span>
              <strong>{{ destination.name }}</strong> (Lat: {{ destination.latitude }}, Lng: {{ destination.longitude
              }})
            </span>
            <button @click="removeDestination(index)" class="text-sm text-red-600 hover:text-red-800">Remove</button>
          </div>
        </li>
      </ul>
    </draggable>

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

export default {
  name: 'TripPlanner',
  components: {
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
      if (this.destinations.length < 2) {
        console.error('Need at least two destinations to calculate journey.');
        return;
      }
      const waypoints = this.destinations.slice(1, -1).map(destination => ({
        location: { lat: destination.latitude, lng: destination.longitude },
        stopover: true,
      }));
      const coord1 = this.destinations[0]
      const coord2 = this.destinations[1]
      // axios.post('/api/calculate-distance-time', {
      //   origin: this.destinations[0].name,
      //   destination: this.destinations[this.destinations.length - 1].name,
      //   waypoints: waypoints.map(wp => `${wp.location.lat},${wp.location.lng}`),
      // })
      //   .then(response => {
      //     this.totalDistance = response.data.distance;
      //     this.totalTime = response.data.duration;
      //   })
      //   .catch(error => {
      //     console.error('Error recalculating journey:', error);
      //   });

      //Get coordinates for Addresses
      axios.get('https://nominatim.openstreetmap.org/search', {
        params: {
          q: 'Lagos', // The address or location name
          format: 'json'
        }
      })
        .then(response => {
          const { lat, lon } = response.data[0]; // Get latitude and longitude from response
          console.log(lat, lon);
          coord2.latitude = lat;
          coord2.longitude = lon;

        })
        .catch(error => {
          console.error('Error fetching coordinates:', error);
        });

      //Calculate routes
      axios.get('/proxy-osrm', {
        params: {
          lat1: coord1.latitude,
          lng1: coord1.longitude,
          lat2: coord2.latitude,
          lng2: coord2.longitude
        }
      })
        .then(response => {
          const route = response.data.routes[0];
          const totalDistance = (route.distance / 1000).toFixed(2); // in kilometers
          const totalTime = (route.duration / 3600).toFixed(2); // in hours

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
