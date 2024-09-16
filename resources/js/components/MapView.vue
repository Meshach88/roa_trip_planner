<template>
  <div>
    <div id="map" style="height: 500px;"></div>
  </div>
</template>

<script>
export default {
  name: "MapView",
  props: {
    destinations: Array,
  },
  data() {
    return {
      map: null,  // Reference to the map
    };
  },
  mounted() {
    if (this.map) {
      this.map = this.map.off()
      // Initialize the map when the component is mounted
      this.map.on()
      this.map = L.map("map").setView([9.082, 8.6753], 6); // Example coordinates (Nigeria)

      // Add a tile layer (OpenStreetMap)
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution:
          '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      }).addTo(this.map);
    }

    // Add markers for the destinations
    this.addMarkers();
  },
  watch: {
    destinations() {
      this.addMarkers(); // Update markers if destinations change
    },
  },
  methods: {
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
          const marker = L.marker([destination.latitude, destination.longitude])
            .addTo(this.map)
            .bindPopup(destination.name);
        });
      }
    },
  },
  beforeDestroy() {
    if (this.map) {
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
