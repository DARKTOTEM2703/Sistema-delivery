<script setup>
import { ref, inject, computed } from 'vue';

const auth = inject('auth');
const showUserMenu = ref(false);
const showMobileMenu = ref(false); // Nueva variable para controlar el menú móvil

// ✅ Variable para el tema oscuro
const isDarkMode = ref(localStorage.getItem('darkMode') === 'true');

defineProps({
  cartTotal: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['open-cart']);

function handleCartClick() {
  emit('open-cart');
}

function toggleDarkMode() {
  isDarkMode.value = !isDarkMode.value;
  document.documentElement.classList.toggle('dark-mode', isDarkMode.value);
  localStorage.setItem('darkMode', isDarkMode.value);
}

function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value;
}

function toggleMobileMenu() {
  showMobileMenu.value = !showMobileMenu.value;
  // Cerrar el menú de usuario si está abierto
  if (showMobileMenu.value) {
    showUserMenu.value = false;
  }
}

function handleLogout() {
  auth.logout();
  showUserMenu.value = false;
  showMobileMenu.value = false; // Cerrar también el menú móvil
}

// Aplicar tema oscuro al cargar si está guardado
if (isDarkMode.value) {
  document.documentElement.classList.add('dark-mode');
}

// Variables reactivas para restaurantes
const hasRestaurant = computed(() => {
  const user = auth.getUser();
  return user?.role === 'owner' || user?.owned_restaurant_id;
});

const restaurantId = computed(() => {
  const user = auth.getUser();
  return user?.owned_restaurant_id || null;
});
</script>

<template>
  <div class="topbar">
    <div class="container">
      <!-- Logo y navegación -->
      <div class="brand">
        <router-link to="/" class="brand-link">
          <h1 class="brand-name">DeliveryApp</h1>
          <span class="restaurant-badge">Multi-Restaurante</span>
        </router-link>
      </div>

      <!-- Botón de menú para móviles -->
      <button class="mobile-menu-toggle" @click="toggleMobileMenu">
        <span class="burger-icon">☰</span>
      </button>

      <!-- Navegación principal y acciones que se mostrarán/ocultarán en móvil -->
      <div class="nav-container" :class="{ 'mobile-menu-open': showMobileMenu }">
        <nav class="main-nav">
          <router-link to="/" class="nav-link" @click="showMobileMenu = false">Inicio</router-link>
          <router-link to="/restaurants" class="nav-link" @click="showMobileMenu = false">Restaurantes</router-link>
        </nav>
        
        <div class="actions">
          <!-- Usuario o botón de login -->
          <div v-if="auth.isAuthenticated()" class="user-dropdown">
            <button class="user-btn" @click="toggleUserMenu">
              {{ auth.getUser().name }}
              <span class="dropdown-icon">▼</span>
            </button>
            <div v-if="showUserMenu" class="dropdown-menu">
              <router-link to="/profile" @click="showUserMenu = false; showMobileMenu = false">Mi Perfil</router-link>
              <router-link to="/orders" @click="showUserMenu = false; showMobileMenu = false">Mis Pedidos</router-link>
              
              <div class="dropdown-divider"></div>
              
              <template v-if="hasRestaurant">
                <router-link :to="`/owner/dashboard/${restaurantId}`" class="dashboard-link" @click="showUserMenu = false; showMobileMenu = false">
                  Panel de Control
                </router-link>
                <router-link :to="`/pos/${restaurantId}`" class="restaurant-link" @click="showUserMenu = false; showMobileMenu = false">
                  Terminal POS
                </router-link>
              </template>
              
              <div class="dropdown-divider"></div>
              
              <a href="#" @click.prevent="handleLogout">Cerrar Sesión</a>
            </div>
          </div>
          <button v-else class="login-btn" @click="auth.showLoginModal(); showMobileMenu = false">
            Iniciar sesión
          </button>
          
          <!-- Carrito -->
          <button class="cart-button" @click="handleCartClick(); showMobileMenu = false">
            <span class="cart-icon">🛒</span>
            <span class="price">${{ cartTotal.toFixed(2) }}</span>
          </button>
          
          <!-- Toggle para tema oscuro -->
          <button class="theme-toggle" @click="toggleDarkMode">
            {{ isDarkMode ? '🌙' : '☀️' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.topbar {
  background-color: var(--topbar-bg);
  border-bottom: 1px solid var(--border-color);
  padding: 15px 0;
  width: 100%;
  transition: background-color 0.3s;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand {
  display: flex;
  align-items: center;
}

.brand-name {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0;
  color: var(--text-color);
}

.restaurant-badge {
  font-size: 0.8rem;
  color: var(--text-color);
  background-color: var(--card-bg);
  padding: 3px 8px;
  border-radius: 4px;
  margin-left: 8px;
  border: 1px solid var(--border-color);
}

.nav-container {
  display: flex;
  align-items: center;
  gap: 16px;
}

.actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.theme-toggle {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  color: var(--text-color);
  transition: color 0.2s;
}

.cart-button {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  padding: 8px 16px;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s;
  color: var(--text-color);
}

.cart-button:hover {
  background-color: #f9f9f9;
  border-color: #ddd;
}

.cart-button:active {
  transform: scale(0.98);
}

.cart-icon {
  margin-right: 4px;
}

.price {
  font-weight: bold;
  font-size: 1rem;
  color: var(--text-color);
}

/* Variables para el tema */
:root {
  --background-color: #f8f8f8;
  --text-color: #333;
  --card-bg: #fff;
  --border-color: #eaeaea;
  --topbar-bg: #fff;
}

.dark-mode {
  --background-color: #121212;
  --text-color: #e0e0e0;
  --card-bg: #1e1e1e;
  --border-color: #333;
  --topbar-bg: #1e1e1e;
}

body {
  background-color: var(--background-color);
  color: var(--text-color);
}

/* Estilos para los elementos de usuario */
.user-dropdown {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  background: none;
  border: none;
  color: var(--text-color);
  cursor: pointer;
  padding: 5px;
}

.dropdown-icon {
  font-size: 0.7rem;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background-color: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 4px;
  padding: 0.5rem 0;
  min-width: 150px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  z-index: 10;
  margin-top: 0.5rem;
}

.dropdown-menu a {
  display: block;
  padding: 0.5rem 1rem;
  color: var(--text-color);
  text-decoration: none;
}

.dropdown-menu a:hover {
  background-color: rgba(128, 128, 128, 0.1);
}

.login-btn {
  background: none;
  border: none;
  color: var(--text-color);
  cursor: pointer;
  padding: 5px 10px;
}

.login-btn:hover {
  text-decoration: underline;
}

.main-nav {
  display: flex;
  gap: 1.5rem;
}

.nav-link {
  color: var(--text-color);
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  transition: all 0.2s;
}

.nav-link:hover,
.nav-link.router-link-active {
  background: rgba(255, 123, 0, 0.1);
  color: #ff7b00;
}

.brand-link {
  text-decoration: none;
  color: inherit;
}

/* Estilos para los enlaces especiales */
.dropdown-divider {
  height: 1px;
  background-color: var(--border-color);
  margin: 0.5rem 0;
}

.restaurant-link,
.dashboard-link {
  display: block;
  padding: 0.5rem 1rem;
  color: var(--text-color);
  text-decoration: none;
  font-weight: 500;
}

.restaurant-link {
  background: linear-gradient(135deg, #ff7b00, #ff9f40);
  color: white !important;
  margin: 0.25rem 0.5rem;
  border-radius: 6px;
}

.restaurant-link:hover {
  background: linear-gradient(135deg, #e66900, #ff8c1a);
}

.dashboard-link {
  background: linear-gradient(135deg, #3b82f6, #60a5fa);
  color: white !important;
  margin: 0.25rem 0.5rem;
  border-radius: 6px;
}

.dashboard-link:hover {
  background: linear-gradient(135deg, #2563eb, #3b82f6);
}

/* Ocultar el botón de menú móvil por defecto */
.mobile-menu-toggle {
  display: none;
  background: none;
  border: none;
  color: var(--text-color);
  font-size: 1.5rem;
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.mobile-menu-toggle:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

/* Media queries para hacer responsive */
@media (max-width: 768px) {
  .topbar {
    padding: 12px 0;
  }
  
  .container {
    flex-wrap: wrap;
  }
  
  .mobile-menu-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .nav-container {
    display: none;
    width: 100%;
    flex-direction: column;
    align-items: flex-start;
    margin-top: 15px;
    gap: 15px;
    background-color: var(--card-bg);
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
  }
  
  .nav-container.mobile-menu-open {
    display: flex;
    animation: slideDown 0.3s ease-out;
  }
  
  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  .main-nav {
    width: 100%;
    flex-direction: column;
    gap: 8px;
  }
  
  .nav-link {
    display: block;
    width: 100%;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 1.1rem;
  }
  
  .actions {
    width: 100%;
    justify-content: space-between;
    padding-top: 12px;
    border-top: 1px solid var(--border-color);
    margin-top: 8px;
  }
  
  /* Mejorar menú desplegable en móvil */
  .dropdown-menu {
    position: static;
    width: 100%;
    margin-top: 10px;
    box-shadow: none;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    animation: fadeIn 0.2s ease-out;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  
  .user-dropdown {
    width: 100%;
  }
  
  .user-btn {
    justify-content: space-between;
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    background-color: rgba(0, 0, 0, 0.03);
  }
  
  .cart-button, .theme-toggle, .login-btn {
    padding: 10px 14px;
    height: 42px;
  }
}

/* Para móviles pequeños */
@media (max-width: 480px) {
  .brand-name {
    font-size: 1.2rem;
  }
  
  .actions {
    flex-wrap: wrap;
    gap: 10px;
  }
  
  .cart-button {
    padding: 8px 12px;
    font-size: 0.9rem;
  }
  
  .restaurant-link, 
  .dashboard-link {
    width: 100%;
    text-align: center;
    margin: 8px 0;
    padding: 10px;
  }
  
  .dropdown-menu a {
    padding: 12px 16px;
    font-size: 1rem;
  }
}

/* Para pantallas muy pequeñas */
@media (max-width: 360px) {
  .container {
    padding: 0 10px;
  }
  
  .actions {
    justify-content: center;
  }
  
  .brand-name {
    font-size: 1.1rem;
  }
}
</style>