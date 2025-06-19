<script setup>
import { ref, provide } from 'vue';
import topbar from './components/topbar.vue';
import hero from './components/hero.vue';
import ProductList from './components/ProductList.vue';
import CartSidebar from './components/CartSidebar.vue';

// Estado del carrito
const cartItems = ref([]);
const isSidebarOpen = ref(false);
const cartTotal = ref(0);

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

// Proporcionar el estado y las funciones a los componentes hijos
provide('cart', {
  items: cartItems,
  total: cartTotal,
  addToCart,
  updateCartItem,
  removeCartItem,
  openSidebar
});
</script>

<template>
  <topbar @open-cart="openSidebar" :cartTotal="cartTotal" />
  <hero />
  <main class="main-container">
    <ProductList @add-to-cart="addToCart" />
  </main>
  
  <CartSidebar 
    :cartItems="cartItems" 
    :isOpen="isSidebarOpen"
    @close="closeSidebar"
    @update-item="updateCartItem"
    @remove-item="removeCartItem"
  />
</template>

<style>
/* Estilos globales */
body {
  margin: 0;
  padding: 0;
  font-family: Arial, Helvetica, sans-serif;
  background-color: #f8f8f8;
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
}

.sidebar-overlay.is-active {
  display: block;
}
</style>
