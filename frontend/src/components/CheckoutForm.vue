<template>
  <div class="checkout-container" v-if="isCheckoutActive">
    <div class="checkout-form">
      <h2>Finalizar Pedido</h2>
      
      <div class="form-group">
        <label for="name">Nombre completo</label>
        <input type="text" id="name" v-model="formData.name" required>
      </div>
      
      <div class="form-group">
        <label for="address">Dirección de entrega</label>
        <input type="text" id="address" v-model="formData.address" required>
      </div>
      
      <div class="form-group">
        <label for="phone">Teléfono</label>
        <input type="tel" id="phone" v-model="formData.phone" required>
      </div>
      
      <div class="form-group">
        <label for="paymentMethod">Método de pago</label>
        <select id="paymentMethod" v-model="formData.paymentMethod" required>
          <option value="efectivo">Efectivo</option>
          <option value="tarjeta">Tarjeta al entregar</option>
          <option value="online">Pago online</option>
        </select>
      </div>
      
      <div class="error-message" v-if="error">{{ error }}</div>
      
      <div class="actions">
        <button class="cancel-btn" @click="$emit('cancel')">Cancelar</button>
        <button class="submit-btn" @click="submitOrder" :disabled="isSubmitting">
          <span v-if="isSubmitting" class="loader"></span>
          Confirmar pedido
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../services/api';

const props = defineProps({
  isCheckoutActive: Boolean,
  cartItems: Array
});

const emit = defineEmits(['cancel', 'order-completed']);

const formData = ref({
  name: '',
  address: '',
  phone: '',
  paymentMethod: 'efectivo'
});

const isSubmitting = ref(false);
const error = ref('');

async function submitOrder() {
  if (!validateForm()) {
    return;
  }
  
  isSubmitting.value = true;
  error.value = '';
  
  try {
    const orderData = {
      customerInfo: formData.value,
      items: props.cartItems,
      total: props.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    };
    
    const response = await api.createOrder(orderData);
    
    emit('order-completed', {
      ...orderData,
      orderId: response.data.order_id,
      orderDate: new Date().toISOString()
    });
    
    // Limpiar formulario
    formData.value = {
      name: '',
      address: '',
      phone: '',
      paymentMethod: 'efectivo'
    };
    
  } catch (err) {
    console.error('Error al procesar el pedido:', err);
    error.value = err.response?.data?.message || 'Error al procesar el pedido';
  } finally {
    isSubmitting.value = false;
  }
}

function validateForm() {
  if (!formData.value.name.trim()) {
    error.value = 'Por favor ingresa tu nombre';
    return false;
  }
  
  if (!formData.value.address.trim()) {
    error.value = 'Por favor ingresa tu dirección';
    return false;
  }
  
  if (!formData.value.phone.trim()) {
    error.value = 'Por favor ingresa tu teléfono';
    return false;
  }
  
  return true;
}
</script>

<style scoped>
.checkout-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(2px);
}

.checkout-form {
  background-color: var(--card-bg);
  border-radius: 8px;
  padding: 2rem;
  width: 90%;
  max-width: 500px;
  box-shadow: var(--box-shadow);
}

h2 {
  color: var(--text-color);
  margin-top: 0;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

input, select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background-color: var(--card-bg);
  color: var(--text-color);
}

.error-message {
  color: red;
  margin-bottom: 1rem;
}

.actions {
  display: flex;
  justify-content: space-between;
  margin-top: 1.5rem;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  background: none;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  color: var(--text-color);
  cursor: pointer;
}

.submit-btn {
  padding: 0.75rem 1.5rem;
  background: var(--button-primary);
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  position: relative;
}

.loader {
  width: 1rem;
  height: 1rem;
  border: 2px solid white;
  border-top-color: transparent;
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  0% { transform: translate(-50%, -50%) rotate(0deg); }
  100% { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>