<template>
  <div>
    <div id="map" style="height: 500px;"></div>
  </div>
</template>

<script>
export default {
  name: "MapView",
  props: {
    destinations: {
      type: Array,
      default: () => [], // Default to an empty array
    },
  },
  data() {
    return {
      map: null,  // Reference to the map
    };
  },
  mounted() {
    // Use $nextTick to ensure DOM is fully rendered before initializing the map
    this.$nextTick(() => {
      if (!this.map) {
        this.beforeInitializeMap();
        this.initializeMap();
      }
      this.addMarkers(); // Add markers after the map is initialized
      this.enableClickToAddMarker(); // Enable click-to-add marker functionality
    });
  },
  watch: {
    destinations() {
      this.addMarkers(); // Update markers if destinations change
    },
  },
  methods: {
    beforeInitializeMap() {
      // Ensure Leaflet is not trying to initialize multiple times
      const container = L.DomUtil.get('map');
      if (container != null) {
        container._leaflet_id = null; // Reset the leaflet map container
      }
    },
    initializeMap() {
      // Initialize the Leaflet map with a default view
      this.map = L.map("map").setView([9.082, 8.6753], 6); // Example coordinates

      // Add a tile layer (OpenStreetMap)
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution:
          '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      }).addTo(this.map);
    },
    
    addMarkers() {
      if (this.map) {
        // Clear previous markers
        this.map.eachLayer((layer) => {
          if (layer instanceof L.Marker) {
            this.map.removeLayer(layer);
          }
        });

        // Add new markers for each destination
        this.destinations.forEach((destination) => {
          L.marker([destination.latitude, destination.longitude])
            .addTo(this.map)
            .bindPopup(destination.name);
        });
      }
    },

    enableClickToAddMarker() {
      // Add a click event listener to the map
      this.map.on('click', (e) => {
        const { lat, lng } = e.latlng;

        // Add a marker at the clicked location
        const marker = L.marker([lat, lng]).addTo(this.map);

        // Optionally bind a popup to the marker
        marker.bindPopup(`Lat: ${lat}, Lng: ${lng}`).openPopup();

        // Emit an event to inform the parent component of the new destination
        this.$emit('add-destination', { latitude: lat, longitude: lng, name: `New Location` });
      });
    }
  },
  beforeUnmount() {
    if (this.map) {
      this.map.off('click'); // Remove the click listener when the component is unmounted
      this.map.remove(); // Remove the map instance to avoid reinitialization issues
      this.map = null;
    }
  },
};
</script>

<style scoped>
#map {
  height: 500px;
}
</style>
