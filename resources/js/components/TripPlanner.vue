<template>
    <div>
      <h1>Road Trip Planner</h1>
      <div class="trip-planner">
        <!-- Form to add a destination -->
        <form @submit.prevent="addDestination">
          <input v-model="newDestination.name" placeholder="Enter destination name" />
          <button type="submit">Add Destination</button>
        </form>
  
        <!-- List of destinations with drag-and-drop functionality -->
        <h2>Destinations</h2>
        <draggable v-model="destinations" class="drag-area" @end="recalculateJourney">
          <div v-for="(destination, index) in destinations" :key="index" class="destination-item">
            <p>{{ index + 1 }}. {{ destination.name }}</p>
            <button @click="removeDestination(index)">Remove</button>
          </div>
        </draggable>
  
        <!-- Map displaying destinations -->
        <h2>Map</h2>
        <div id="map" ref="map" style="height: 400px;"></div>
  
        <!-- Journey summary data -->
        <h2>Journey Summary</h2>
        <p>Total Distance: {{ totalDistance }} km</p>
        <p>Total Time: {{ totalTime }} mins</p>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted, watch } from 'vue';
  import draggable from 'vuedraggable'; // Install with npm install vuedraggable
  import { Loader } from "@googlemaps/js-api-loader"; // Google Maps API loader
  
  export default {
    components: {
      draggable,
    },
    data() {
      return {
        newDestination: {
          name: '',
          latitude: null,
          longitude: null,
        },
        destinations: [], // List of added destinations
        totalDistance: 0,
        totalTime: 0,
        map: null,
        markers: [],
        directionsService: null,
        directionsRenderer: null,
      };
    },
    methods: {
      addDestination() {
        if (this.newDestination.name) {
          this.destinations.push({ ...this.newDestination });
          this.saveToLocalStorage();
          this.newDestination.name = '';
          this.recalculateJourney();
        }
      },
      removeDestination(index) {
        this.destinations.splice(index, 1);
        this.saveToLocalStorage();
        this.recalculateJourney();
      },
      saveToLocalStorage() {
        localStorage.setItem('destinations', JSON.stringify(this.destinations));
      },
      recalculateJourney() {
        if (this.destinations.length < 2) return;
  
        // Clear previous markers and directions
        this.markers.forEach(marker => marker.setMap(null));
        this.directionsRenderer.setMap(null);
  
        const waypoints = this.destinations.slice(1, -1).map(destination => ({
          location: { lat: destination.latitude, lng: destination.longitude },
          stopover: true,
        }));
  
        const request = {
          origin: { lat: this.destinations[0].latitude, lng: this.destinations[0].longitude },
          destination: { lat: this.destinations[this.destinations.length - 1].latitude, lng: this.destinations[this.destinations.length - 1].longitude },
          waypoints: waypoints,
          travelMode: 'DRIVING',
        };
  
        this.directionsService.route(request, (result, status) => {
          if (status === 'OK') {
            this.directionsRenderer.setDirections(result);
            this.calculateSummary(result);
          }
        });
      },
      calculateSummary(result) {
        let totalDistance = 0;
        let totalTime = 0;
  
        const legs = result.routes[0].legs;
        legs.forEach(leg => {
          totalDistance += leg.distance.value / 1000; // Convert meters to km
          totalTime += leg.duration.value / 60; // Convert seconds to minutes
        });
  
        this.totalDistance = totalDistance.toFixed(2);
        this.totalTime = totalTime.toFixed(2);
      },
    },
    mounted() {
      const loader = new Loader({
        apiKey: 'YOUR_GOOGLE_MAPS_API_KEY',
        version: 'weekly',
        libraries: ['places']
      });
  
      loader.load().then(() => {
        this.map = new google.maps.Map(this.$refs.map, {
          center: { lat: -34.397, lng: 150.644 },
          zoom: 8,
        });
  
        this.directionsService = new google.maps.DirectionsService();
        this.directionsRenderer = new google.maps.DirectionsRenderer({ map: this.map });
  
        // Load saved data from local storage
        const savedDestinations = localStorage.getItem('destinations');
        if (savedDestinations) {
          this.destinations = JSON.parse(savedDestinations);
          this.recalculateJourney();
        }
  
        // Add click event on map to add destination with lat/lng
        this.map.addListener('click', (event) => {
          this.newDestination.latitude = event.latLng.lat();
          this.newDestination.longitude = event.latLng.lng();
          this.addDestination();
        });
      });
    },
    watch: {
      destinations: {
        handler(newDestinations) {
          this.saveToLocalStorage();
          this.recalculateJourney();
        },
        deep: true,
      },
    },
  };
  </script>
  
  <style scoped>
  .trip-planner {
    max-width: 600px;
    margin: auto;
  }
  .drag-area {
    padding: 10px;
    border: 1px solid #ccc;
    margin-bottom: 20px;
  }
  .destination-item {
    padding: 5px;
    background: #f9f9f9;
    margin-bottom: 5px;
    border: 1px solid #ccc;
  }
  </style>
  