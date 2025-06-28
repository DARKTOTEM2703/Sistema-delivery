<script setup>
import { ref, inject } from 'vue';

const isDarkMode = ref(localStorage.getItem('darkMode') === 'true');
const auth = inject('auth'); // Inyectar el servicio de autenticación
const showUserMenu = ref(false);

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

function handleLogout() {
  auth.logout();
  showUserMenu.value = false;
}

// Aplicar tema oscuro al cargar si está guardado
if (isDarkMode.value) {
  document.documentElement.classList.add('dark-mode');
}
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

      <!-- Navegación principal -->
      <nav class="main-nav">
        <router-link to="/" class="nav-link">Inicio</router-link>
        <router-link to="/restaurants" class="nav-link">Restaurantes</router-link>
      </nav>
      
      <div class="actions">
        <!-- Usuario o botón de login -->
        <div v-if="auth.isAuthenticated()" class="user-dropdown">
          <button class="user-btn" @click="toggleUserMenu">
            {{ auth.getUser().name }}
            <span class="dropdown-icon">▼</span>
          </button>
          <div v-if="showUserMenu" class="dropdown-menu">
            <router-link to="/profile" @click="showUserMenu = false">Mi Perfil</router-link>
            <a href="#" @click.prevent="handleLogout">Cerrar sesión</a>
          </div>
        </div>
        <button v-else class="login-btn" @click="auth.showLoginModal()">
          Iniciar sesión
        </button>
        
        <!-- Carrito -->
        <button class="cart-button" @click="handleCartClick">
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
</template>

<style scoped>
.topbar {
  background-color: var(--topbar-bg);
  border-bottom: 1px solid var(--border-color);
  padding: 15px 0;
  width: 100%;
  transition: background-color 0.3s;
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

/* Añadir en el archivo de estilos global */
:root {
  --background-color: #f8f8f8;
  --text-color: #333;
  --card-bg: #fff;
  --border-color: #eaeaea;
}

.dark-mode {
  --background-color: #121212;
  --text-color: #e0e0e0;
  --card-bg: #1e1e1e;
  --border-color: #333;
}

body {
  background-color: var(--background-color);
  color: var(--text-color);
}

/* Añade estos estilos para los elementos de usuario */
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
  box-shadow: var(--box-shadow);
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
</style>