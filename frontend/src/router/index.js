import { createRouter, createWebHistory } from "vue-router";
import Home from "../views/Home.vue";
import RestaurantList from "../components/RestaurantList.vue";
import RestaurantMenu from "../views/RestaurantMenu.vue";
import ProfileView from "../views/ProfileView.vue";
import auth from "../services/auth";

const routes = [
  {
    path: "/",
    name: "home",
    component: Home,
  },
  {
    path: "/restaurants",
    name: "restaurants",
    component: RestaurantList,
  },
  {
    path: "/restaurant/:id",
    name: "restaurant-menu",
    component: RestaurantMenu,
  },
  {
    path: "/profile",
    name: "profile",
    component: ProfileView,
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navegación protegida
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth && !auth.state.isAuthenticated) {
    // Si la ruta requiere autenticación y no está autenticado
    auth.showLoginModal();
    next(false);
  } else if (to.meta.requiresAdmin && !auth.hasRole("admin")) {
    // Si la ruta requiere rol de admin y no lo tiene
    next({ name: "home" });
  } else {
    next();
  }
});

export default router;
