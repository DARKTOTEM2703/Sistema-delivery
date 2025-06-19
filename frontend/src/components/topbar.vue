<script setup>
import { ref } from 'vue';

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
</script>

<template>
  <div class="topbar">
    <div class="container">
      <!-- Logo y nombre del restaurante -->
      <div class="brand">
        <h1 class="brand-name">Bella Vista</h1>
        <span class="restaurant-badge">Restaurante</span>
      </div>
      
      <!-- Carrito de compras como botón -->
      <button class="cart-button" @click="handleCartClick">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="cart-icon" viewBox="0 0 16 16">
          <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
        </svg>
        <span class="price">${{ cartTotal.toFixed(2) }}</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.topbar {
  background-color: white;
  border-bottom: 1px solid #eaeaea;
  padding: 15px 0;
  width: 100%;
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
  color: #111;
}

.restaurant-badge {
  font-size: 0.8rem;
  color: #555;
  background-color: #f5f5f5;
  padding: 3px 8px;
  border-radius: 4px;
  margin-left: 8px;
  border: 1px solid #e0e0e0;
}

.cart-button {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: white;
  border: 1px solid #eaeaea;
  border-radius: 6px;
  padding: 8px 16px;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s;
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
  color: #333;
}
</style>