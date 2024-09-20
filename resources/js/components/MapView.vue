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
      markers: {},  //Object to store marker references by destination id
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
    destinations: {
      handler() {
        this.addMarkers(); // Update markers if destinations change
      },
      deep: true, // Watch for deep changes in destinations array
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
          // Skip if marker already exists for this destination
          if (this.markers[destination.id]) return;

          const marker = L.marker([destination.latitude, destination.longitude])
            .addTo(this.map)
            .bindPopup(destination.name);

          // Store marker reference using destination ID
          this.markers[destination.id] = marker;
        });
        this.map.dragging.enable();
        this.map.scrollWheelZoom.enable();
      }
    },


    enableClickToAddMarker() {
      // Add a click event listener to the map
      this.map.on('click', async (e) => {
        const { lat, lng } = e.latlng;

        try {
          const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`
          );

          if (!response.ok) {
            throw new Error('Failed to fetch location name');
          }
          const data = await response.json();

          const name = data.display_name || "New Location";

          // Add a marker at the clicked location
          const marker = L.marker([lat, lng]).addTo(this.map);

          // Optionally bind a popup to the marker
          marker.bindPopup(`Name: ${name}, Lat: ${lat.toFixed(2)}, Lng: ${lng.toFixed(2)}`).openPopup();

          // Emit an event to inform the parent component of the new destination
          this.$emit('add-destination', { latitude: lat, longitude: lng, name });
        } catch (error) {
          console.error('Error getting location name:', error);
        }
      });
    },
    clearOldMarkers() {
      // Remove markers that are no longer in the destinations array
      Object.keys(this.markers).forEach((id) => {
        const destinationExists = this.destinations.find(dest => dest.id === id);
        if (!destinationExists) {
          // Remove marker from the map and delete from markers object
          this.map.removeLayer(this.markers[id]);
          delete this.markers[id];
        }
      });
    },

    removeMarkerById(id) {
      // Remove marker for a specific destination ID
      if (this.markers[id]) {
        this.map.removeLayer(this.markers[id]);
        delete this.markers[id]; // Remove from markers object
      }
    },
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
