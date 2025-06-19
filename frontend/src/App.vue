<script setup>
import { ref, provide, onMounted, watch } from 'vue';
import topbar from './components/topbar.vue';
import hero from './components/hero.vue';
import ProductList from './components/ProductList.vue';
import CartSidebar from './components/CartSidebar.vue';
import Toast from './components/Toast.vue';
import SearchBar from './components/SearchBar.vue';

// Estado del carrito
const cartItems = ref([]);
const isSidebarOpen = ref(false);
const cartTotal = ref(0);
const showToast = ref(false);
const toastMessage = ref('');
const searchQuery = ref('');

// Verificar modo oscuro al iniciar
onMounted(() => {
  // Cargar carrito desde localStorage
  const savedCart = localStorage.getItem('cart');
  if (savedCart) {
    cartItems.value = JSON.parse(savedCart);
    calculateTotal();
  }
  
  // Verificar si hay tema oscuro guardado
  const isDarkMode = localStorage.getItem('darkMode') === 'true';
  if (isDarkMode) {
    document.documentElement.classList.add('dark-mode');
  }
});

// Guardar carrito en localStorage cuando cambie
watch(cartItems, (newItems) => {
  localStorage.setItem('cart', JSON.stringify(newItems));
}, { deep: true });

// Calcular el total del carrito
function calculateTotal() {
  cartTotal.value = cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
}

// Añadir un item al carrito
function addToCart(product) {
  const existingItem = cartItems.value.find(item => item.id === product.id);
  
  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    cartItems.value.push({
      ...product,
      quantity: 1
    });
  }
  
  calculateTotal();
  
  // Mostrar notificación
  toastMessage.value = `${product.name} añadido al carrito`;
  showToast.value = true;
  setTimeout(() => {
    showToast.value = false;
  }, 3000);
}

// Actualizar un item en el carrito
function updateCartItem(updatedItem) {
  const index = cartItems.value.findIndex(item => item.id === updatedItem.id);
  if (index !== -1) {
    cartItems.value[index] = updatedItem;
    calculateTotal();
  }
}

// Eliminar un item del carrito
function removeCartItem(itemToRemove) {
  cartItems.value = cartItems.value.filter(item => item.id !== itemToRemove.id);
  calculateTotal();
}

// Abrir el sidebar
function openSidebar() {
  isSidebarOpen.value = true;
}

// Cerrar el sidebar
function closeSidebar() {
  isSidebarOpen.value = false;
}

// Manejar búsqueda
function handleSearch(query) {
  searchQuery.value = query;
}

// Proporcionar el estado y las funciones a los componentes hijos
provide('cart', {
  items: cartItems,
  total: cartTotal,
  addToCart,
  updateCartItem,
  removeCartItem,
  openSidebar
});

provide('search', {
  query: searchQuery
});
</script>

<template>
  <topbar @open-cart="openSidebar" :cartTotal="cartTotal" />
  <hero />
  <main class="main-container">
    <!-- Barra de búsqueda -->
    <SearchBar @search="handleSearch" />
    
    <ProductList 
      @add-to-cart="addToCart" 
      :searchQuery="searchQuery"
    />
  </main>
  
  <div 
    class="sidebar-overlay" 
    :class="{ 'is-active': isSidebarOpen }"
    @click="closeSidebar"
  ></div>
  
  <CartSidebar 
    :cartItems="cartItems" 
    :isOpen="isSidebarOpen"
    @close="closeSidebar"
    @update-item="updateCartItem"
    @remove-item="removeCartItem"
  />

  <!-- Componente Toast para notificaciones -->
  <Toast :show="showToast" :message="toastMessage" />
</template>

<style>
/* Estilos globales */
:root {
  --background-color: #f8f8f8;
  --text-color: #333;
  --card-bg: #fff;
  --border-color: #eaeaea;
  --sidebar-bg: #fff;
  --box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  --topbar-bg: #fff;
  --button-primary: linear-gradient(to right, #ff7b00, #ff0000);
  --button-hover: #ff6b00;
}

.dark-mode {
  --background-color: #121212;
  --text-color: #e0e0e0;
  --card-bg: #1e1e1e;
  --border-color: #333;
  --sidebar-bg: #1e1e1e;
  --box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
  --topbar-bg: #1a1a1a;
  --button-primary: linear-gradient(to right, #ff7b00, #ff0000);
  --button-hover: #ff8c00;
}

body {
  margin: 0;
  padding: 0;
  font-family: Arial, Helvetica, sans-serif;
  background-color: var(--background-color);
  color: var(--text-color);
  transition: background-color 0.3s, color 0.3s;
}

.main-container {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 0 15px;
}

/* Overlay para cuando el sidebar está abierto */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: none;
  backdrop-filter: blur(2px);
}

.sidebar-overlay.is-active {
  display: block;
}

/* Estilos para botones principales */
.primary-button {
  background: var(--button-primary);
  color: white;
  border: none;
  border-radius: 6px;
  padding: 10px 20px;
  cursor: pointer;
  transition: all 0.2s;
}

.primary-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
</style>
