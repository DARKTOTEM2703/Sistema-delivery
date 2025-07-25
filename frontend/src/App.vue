<script setup>
import { ref, provide, onMounted, watch } from 'vue';
import topbar from './components/topbar.vue';
import CartSidebar from './components/CartSidebar.vue';
import Toast from './components/Toast.vue';
import CheckoutForm from './components/CheckoutForm.vue';
import Login from './components/Login.vue';
import auth from './services/auth';
import { useNotifications } from './services/useNotifications';

// ✅ AGREGAR EL SISTEMA DE NOTIFICACIONES
const { notifications, success: showSuccess, error: showError } = useNotifications();

// Estado del carrito
const cartItems = ref([]);
const isSidebarOpen = ref(false);
const cartTotal = ref(0);
const isCheckoutActive = ref(false);

// Bandera para prevenir múltiples clics
const isAddingToCart = ref(false);

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

// Añadir un item al carrito con protección contra doble clic
function addToCart(product) {
  // Prevenir múltiples clics rápidos
  if (isAddingToCart.value) return;
  
  isAddingToCart.value = true;
  
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
  
  // Usar el sistema de notificaciones
  showSuccess(`${product.name} añadido al carrito`);
  
  // Restablecer la bandera después de un breve retraso
  setTimeout(() => {
    isAddingToCart.value = false;
  }, 300);
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

// Función para iniciar checkout
function startCheckout() {
  isCheckoutActive.value = true;
  closeSidebar();
}

// Función para finalizar checkout
function finishCheckout(orderData) {
  // Limpiar carrito
  cartItems.value = [];
  calculateTotal();
  
  // Cerrar checkout
  isCheckoutActive.value = false;
  
  // Mostrar confirmación
  showSuccess("¡Pedido realizado con éxito!");
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

// ✅ PROPORCIONAR NOTIFICACIONES
provide('notifications', {
  success: showSuccess,
  error: showError,
  notifications
});

// Proporciona el servicio de autenticación a todos los componentes
provide('auth', auth);
</script>

<template>
  <div id="app">
    <!-- Topbar siempre visible -->
    <topbar @open-cart="openSidebar" :cartTotal="cartTotal" />
    
    <!-- Router View -->
    <router-view v-slot="{ Component }">
      <transition name="page" mode="out-in">
        <component 
          :is="Component" 
          @add-to-cart="addToCart"
        />
      </transition>
    </router-view>
    
    <!-- Overlay para sidebar -->
    <div 
      class="sidebar-overlay" 
      :class="{ 'is-active': isSidebarOpen }"
      @click="closeSidebar"
    ></div>
    
    <!-- Sidebar del carrito -->
    <CartSidebar 
      :cartItems="cartItems" 
      :isOpen="isSidebarOpen"
      @close="closeSidebar"
      @update-item="updateCartItem"
      @remove-item="removeCartItem"
      @checkout="startCheckout"
    />

    <!-- Componente Toast para notificaciones -->
    <Toast />

    <!-- Formulario de checkout -->
    <CheckoutForm 
      v-if="isCheckoutActive"
      :isCheckoutActive="isCheckoutActive"
      :cartItems="cartItems"
      @cancel="isCheckoutActive = false"
      @order-completed="finishCheckout"
    />

    <!-- Modal de login -->
    <Login v-if="auth.state.showLoginModal" />
  </div>
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
  --topbar-bg: #1e1e1e;
}

body {
  margin: 0;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
    'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
    sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  background-color: var(--background-color);
  color: var(--text-color);
  transition: background-color 0.3s, color 0.3s;
}

#app {
  min-height: 100vh;
}

.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 999;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s, visibility 0.3s;
}

.sidebar-overlay.is-active {
  opacity: 1;
  visibility: visible;
}

/* Reglas responsivas globales */
:root {
  --spacing-unit: 1rem;
}

@media (max-width: 768px) {
  :root {
    --spacing-unit: 0.75rem;
  }
  
  body {
    font-size: 14px;
  }
  
  h1 {
    font-size: 1.8rem;
  }
  
  h2 {
    font-size: 1.5rem;
  }
  
  h3 {
    font-size: 1.3rem;
  }
  
  button {
    min-height: 42px; /* Mejorar toque en móviles */
  }
  
  input, select, textarea {
    font-size: 16px; /* Prevenir zoom en iOS */
  }
}

@media (max-width: 480px) {
  :root {
    --spacing-unit: 0.5rem;
  }
  
  body {
    font-size: 13px;
  }
  
  h1 {
    font-size: 1.6rem;
  }
  
  h2 {
    font-size: 1.3rem;
  }
  
  h3 {
    font-size: 1.1rem;
  }
  
  .page-enter-active,
  .page-leave-active {
    transition-duration: 0.2s;
  }
  
  /* Mejorar espaciado general */
  .container {
    padding: 0.75rem;
  }
}

/* Mejor soporte táctil */
@media (hover: none) {
  .btn, button[type="submit"], .nav-link, a.router-link {
    cursor: default;
  }
}

/* Transiciones de página */
.page-enter-active,
.page-leave-active {
  transition: opacity 0.3s ease;
}

.page-enter-from,
.page-leave-to {
  opacity: 0;
}
</style>
