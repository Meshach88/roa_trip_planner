import './bootstrap';
import { createApp } from 'vue';
import TripPlanner from './components/TripPlanner.vue';

createApp(TripPlanner).mount("#app")



// const app = createApp({
//     components: {
//         TripPlanner,
//     },
// }).mount("#app")

// app.component('trip-planner', TripPlanner);
// app.mount('#app');

// createApp({}).mount("#app")