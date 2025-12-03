import { createRouter, createWebHistory } from "vue-router";
import Home from "../pages/Home.vue";
import CalculatePage from "../pages/CalculatePage.vue";
import StatsPage from "../pages/StatsPage.vue";

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: "/", name: "home", component: Home },
    { path: "/calcul", name: "calculate", component: CalculatePage },
    { path: "/stats", name: "stats", component: StatsPage },
  ],
});