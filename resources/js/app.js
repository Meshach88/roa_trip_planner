import "./bootstrap";
import { createApp } from "vue";
import TripPlanner from "./components/TripPlanner.vue";
import MapView from "./components/MapView.vue";

createApp(TripPlanner).mount("#app");
createApp(MapView).mount("#map");

// const app = createApp({
//     components: {
//         TripPlanner,
//     },
// }).mount("#app")

// app.component('trip-planner', TripPlanner);
// app.mount('#app');

// createApp({}).mount("#app")
