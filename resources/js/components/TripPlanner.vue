<template>
  <div class="trip-planner">
    <h1>Road Trip Planner</h1>

    <!-- Add Destination Form -->
    <form @submit.prevent="addDestination">
      <input type="text" v-model="newDestination.name" placeholder="Destination name" required />
      <input type="number" v-model="newDestination.latitude" placeholder="Latitude" required />
      <input type="number" v-model="newDestination.longitude" placeholder="Longitude" required />
      <button type="submit">Add Destination</button>
    </form>

     <!-- Map Component -->
     <MapView :destinations="destinations" @add-destination="addDestinationFromMap" />

    <!-- Destination List -->
    <ul>
      <li v-for="(destination, index) in destinations" :key="destination.id">
        <div>
          <strong>{{ destination.name }}</strong> (Lat: {{ destination.latitude }}, Lng: {{ destination.longitude }})
          <button @click="removeDestination(index)">Remove</button>
        </div>
      </li>
    </ul>

    <!-- Recalculate Journey -->
    <button @click="recalculateJourney">Recalculate Journey</button>

    <!-- Journey Summary -->
    <div v-if="totalDistance || totalTime">
      <h2>Journey Summary</h2>
      <p>Total Distance: {{ totalDistance }}</p>
      <p>Total Time: {{ totalTime }}</p>
    </div>

    
  </div>
</template>

<script>
import MapView from './MapView.vue';
import axios from 'axios';

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
    //Add destination manually from the form
    addDestination() {
      axios.post('/api/destinations', this.newDestination)
        .then(response => {
          //Push the newly created destination into the destinations array
          this.destinations.push(response.data);

          //Save to local storage for persistence
          this.saveToLocalStorage();

          //Reset the form fields after adding the destination
          this.resetForm();
        })
        .catch(error => {
          console.error('Error adding destination:', error);
        });
    },

    //Add destination from map click
    addDestinationFromMap(destination) {
      this.destinations.push(destination);
      this.saveToLocalStorage();
    },

    //Remove destination
    removeDestination(index) {
      const destinationId = this.destinations[index].id;
      axios.delete(`/api/destinations/${destinationId}`)
        .then(() => {
          //Remove the destination from the local array
          this.destinations.splice(index, 1);

          //Update local storage after removal
          this.saveToLocalStorage();
        })
        .catch(error => {
          console.error('Error removing destination:', error);
        });
    },
    recalculateJourney() {
      if (this.destinations.length < 2) {
        console.error('Need at least two destinations to calculate journey.');
        return;
      }


      //Extract waypoints (all destinations except the first and last)
      const waypoints = this.destinations.slice(1, -1).map(destination => ({
        location: { lat: destination.latitude, lng: destination.longitude },
        stopover: true,
      }));

      //Send the API request with origin, destination and waypoints
      axios.post('/api/calculate-distance-time', {
        origin: this.destinations[0].name,
        destination: this.destinations[this.destinations.length - 1].name,
        waypoints: waypoints.map(wp => `${wp.location.lat},${wp.location.lng}`),
      })
        .then(response => {
          //Set the total distance and time from the response data
          this.totalDistance = response.data.distance;
          this.totalTime = response.data.duration;
        })
        .catch(error => {
          console.error('Error recalculating journey:', error);
        });
    },

    //Save the destination data in the browser's local storage
    saveToLocalStorage() {
      localStorage.setItem('destinations', JSON.stringify(this.destinations));
    },

    //reset form fields
    resetForm() {
      this.newDestination.name = '';
      this.newDestination.latitude = null;
      this.newDestination.longitude = null;
    }
  },

  //Load destination from local storage when component mounts
  mounted() {
    const savedDestinations = localStorage.getItem('destinations');
    if (savedDestinations) {
      this.destinations = JSON.parse(savedDestinations);
    }
  }
};
</script>

<style>
.trip-planner {
  font-family: Arial, sans-serif;
  margin: 0 auto;
  max-width: 600px;
  padding: 20px;
}

form {
  margin-bottom: 20px;
}

input {
  margin-right: 10px;
  padding: 5px;
}

button {
  padding: 5px 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:hover {
  background-color: #0056b3;
}

ul {
  list-style-type: none;
  padding: 0;
}

li {
  margin-bottom: 10px;
}

.map {
  margin-top: 20px;
  padding: 20px;
  border: 1px solid #ccc;
  text-align: center;
}

h1,
h2 {
  color: #007bff;
}
</style>
