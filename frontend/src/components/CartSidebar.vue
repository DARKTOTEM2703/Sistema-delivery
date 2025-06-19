<template>
  <div class="cart-sidebar" :class="{ 'is-open': isOpen }">
    <div class="sidebar-header">
      <h2>Tu Pedido</h2>
      <button class="close-button" @click="$emit('close')">×</button>
    </div>
    
    <div class="sidebar-content">
      <div v-if="cartItems.length === 0" class="empty-cart">
        <p>Tu carrito está vacío</p>
      </div>
      
      <div v-else class="cart-items">
        <div v-for="item in cartItems" :key="item.id" class="cart-item">
          <div class="item-info">
            <h3>{{ item.name }}</h3>
            <p class="item-price">${{ item.price.toFixed(2) }}</p>
          </div>
          <div class="item-actions">
            <button class="quantity-btn" @click="decreaseQuantity(item)">-</button>
            <span class="quantity">{{ item.quantity }}</span>
            <button class="quantity-btn" @click="increaseQuantity(item)">+</button>
            <button class="remove-btn" @click="removeItem(item)">🗑️</button>
          </div>
        </div>
      </div>
    </div>
    
    <div class="sidebar-footer">
      <div class="total">
        <span>Total:</span>
        <span class="total-price">${{ totalPrice.toFixed(2) }}</span>
      </div>
      <button class="checkout-button" :disabled="cartItems.length === 0">
        Completar Pedido
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  cartItems: {
    type: Array,
    required: true
  },
  isOpen: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'update-item', 'remove-item']);

const totalPrice = computed(() => {
  return props.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

function increaseQuantity(item) {
  emit('update-item', { ...item, quantity: item.quantity + 1 });
}

function decreaseQuantity(item) {
  if (item.quantity > 1) {
    emit('update-item', { ...item, quantity: item.quantity - 1 });
  } else {
    removeItem(item);
  }
}

function removeItem(item) {
  emit('remove-item', item);
}
</script>

<style scoped>
.cart-sidebar {
  position: fixed;
  top: 0;
  right: -400px;
  width: 400px;
  height: 100vh;
  background-color: white;
  box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  transition: right 0.3s ease;
  display: flex;
  flex-direction: column;
}

.cart-sidebar.is-open {
  right: 0;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid #eaeaea;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.sidebar-header h2 {
  margin: 0;
  font-size: 1.5rem;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}

.sidebar-content {
  flex-grow: 1;
  overflow-y: auto;
  padding: 1rem;
}

.empty-cart {
  text-align: center;
  color: #888;
  margin-top: 2rem;
}

.cart-item {
  padding: 1rem;
  border-bottom: 1px solid #eaeaea;
  display: flex;
  flex-direction: column;
}

.item-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.item-info h3 {
  margin: 0;
  font-size: 1rem;
}

.item-price {
  font-weight: bold;
  margin: 0;
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.quantity-btn {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
}

.quantity {
  min-width: 20px;
  text-align: center;
}

.remove-btn {
  margin-left: auto;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
}

.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid #eaeaea;
}

.total {
  display: flex;
  justify-content: space-between;
  font-size: 1.25rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.checkout-button {
  width: 100%;
  padding: 1rem;
  background-color: #222;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.checkout-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>