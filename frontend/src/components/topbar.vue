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
      <!-- Logo y nombre del restaurante -->
      <div class="brand">
        <h1 class="brand-name">Bella Vista</h1>
        <span class="restaurant-badge">Restaurante</span>
      </div>
      
      <div class="actions">
        <!-- Usuario o botón de login -->
        <div v-if="auth.isAuthenticated()" class="user-dropdown">
          <button class="user-btn" @click="toggleUserMenu">
            {{ auth.getUser().name }}
            <span class="dropdown-icon">▼</span>
          </button>
          <div v-if="showUserMenu" class="dropdown-menu">
            <a href="#" @click.prevent="handleLogout">Cerrar sesión</a>
          </div>
        </div>
        <button v-else class="login-btn" @click="auth.showLoginModal()">
          Iniciar sesión
        </button>
        
        <!-- Toggle para tema oscuro -->
        <button class="theme-toggle" @click="toggleDarkMode">
          {{ isDarkMode ? '🌙' : '☀️' }}
        </button>
        
        <!-- Carrito de compras como botón -->
        <button class="cart-button" @click="handleCartClick">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="cart-icon" viewBox="0 0 16 16">
            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
          </svg>
          <span class="price">${{ cartTotal.toFixed(2) }}</span>
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
</style>