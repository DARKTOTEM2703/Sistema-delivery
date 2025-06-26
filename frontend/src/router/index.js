import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import AdminPanel from "../components/AdminPanel.vue";
import ProfileView from "../views/ProfileView.vue";
import OrdersHistory from "../views/OrdersHistory.vue";
import OrderTracking from "../views/OrderTracking.vue";
import auth from "../services/auth";

const routes = [
  {
    path: "/",
    name: "home",
    component: HomeView,
  },
  {
    path: "/admin",
    name: "admin",
    component: AdminPanel,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/profile",
    name: "profile",
    component: ProfileView,
    meta: { requiresAuth: true },
  },
  {
    path: "/orders",
    name: "orders",
    component: OrdersHistory,
    meta: { requiresAuth: true },
  },
  {
    path: "/track/:orderId",
    name: "track-order",
    component: OrderTracking,
    props: true,
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
