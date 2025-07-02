<template>
  <div class="checkout-container" v-if="isCheckoutActive">
    <div class="checkout-form">
      <h2>Finalizar Pedido</h2>
      
      <div class="order-summary">
        <h3>Resumen del pedido</h3>
        <div v-for="item in cartItems" :key="item.id" class="order-item">
          <span>{{ item.name }} x{{ item.quantity }}</span>
          <span>${{ (item.price * item.quantity).toFixed(2) }}</span>
        </div>
        <div class="order-total">
          <strong>Total: ${{ orderTotal.toFixed(2) }}</strong>
        </div>
      </div>
      
      <div class="form-group">
        <label for="name">Nombre completo</label>
        <input type="text" id="name" v-model="formData.name" required>
      </div>
      
      <div class="form-group">
        <label for="address">Dirección de entrega</label>
        <input type="text" id="address" v-model="formData.address" required>
        <div class="error-message" v-if="errors.address">{{ errors.address }}</div>
      </div>
      
      <div class="form-group">
        <label for="phone">Teléfono</label>
        <input type="tel" id="phone" v-model="formData.phone" required>
        <div class="error-message" v-if="errors.phone">{{ errors.phone }}</div>
      </div>
      
      <div class="form-group">
        <label for="paymentMethod">Método de pago</label>
        <select id="paymentMethod" v-model="formData.paymentMethod" required>
          <option value="efectivo">Efectivo</option>
          <option value="tarjeta">Tarjeta al entregar</option>
          <option value="online">Pago online</option>
        </select>
        <div class="error-message" v-if="errors.paymentMethod">{{ errors.paymentMethod }}</div>
      </div>
      
      <div class="form-group">
        <label for="specialInstructions">Instrucciones especiales</label>
        <textarea id="specialInstructions" v-model="formData.special_instructions" rows="3"></textarea>
      </div>
      
      <div class="actions">
        <button class="cancel-btn" @click="$emit('cancel')">Cancelar</button>
        <button class="submit-btn" @click="submitOrder" :disabled="isSubmitting">
          <span v-if="isSubmitting" class="loader"></span>
          {{ isSubmitting ? 'Procesando...' : 'Confirmar pedido' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useNotifications } from '@/services/useNotifications';

const props = defineProps({
  isCheckoutActive: Boolean,
  cartItems: Array
});

const emit = defineEmits(['cancel', 'order-completed']);

const { error: showError, success } = useNotifications();

const formData = ref({
  name: '',
  address: '',
  phone: '',
  paymentMethod: 'efectivo',
  special_instructions: ''
});

const errors = ref({});
const isSubmitting = ref(false);

const orderTotal = computed(() => {
  return props.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const isValid = computed(() => {
  return formData.value.address.trim().length >= 10 &&
         isValidPhone(formData.value.phone) &&
         formData.value.paymentMethod;
});

function isValidPhone(phone) {
  const cleanPhone = phone.replace(/\D/g, '');
  return cleanPhone.length >= 8 && cleanPhone.length <= 15;
}

function validateForm() {
  errors.value = {};
  
  if (!formData.value.name.trim()) {
    errors.value.name = 'Por favor ingresa tu nombre';
  }
  
  if (!formData.value.address.trim()) {
    errors.value.address = 'La dirección es requerida';
  } else if (formData.value.address.trim().length < 10) {
    errors.value.address = 'La dirección debe tener al menos 10 caracteres';
  }
  
  if (!formData.value.phone.trim()) {
    errors.value.phone = 'El teléfono es requerido';
  } else if (!isValidPhone(formData.value.phone)) {
    errors.value.phone = 'Formato de teléfono inválido';
  }
  
  if (!formData.value.paymentMethod) {
    errors.value.paymentMethod = 'Selecciona un método de pago';
  }
  
  if (props.cartItems.length === 0) {
    errors.value.cart = 'Tu carrito está vacío';
  }
  
  return Object.keys(errors.value).length === 0;
}

async function submitOrder() {
  if (!validateForm() || isSubmitting.value) return;
  
  isSubmitting.value = true;
  
  try {
    // ✅ OBTENER restaurant_id del primer item del carrito
    const firstItem = props.cartItems[0];
    const restaurantId = firstItem?.restaurant_id || 1; // Fallback a 1
    
    const orderData = {
      restaurant_id: restaurantId, // ✅ AGREGAR ESTA LÍNEA
      items: props.cartItems.map(item => ({
        id: item.id,
        quantity: item.quantity,
        price: item.price
      })),
      total: orderTotal.value,
      address: formData.value.address,
      phone: formData.value.phone,
      payment_method: formData.value.paymentMethod,
      special_instructions: formData.value.special_instructions
    };
    
    console.log('📤 Enviando pedido:', orderData);
    
    const response = await api.createOrder(orderData);
    
    console.log('✅ Respuesta del servidor:', response.data);
    
    emit('order-completed', {
      ...orderData,
      orderId: response.data.order?.id || Math.random().toString(36).substr(2, 9),
      orderDate: new Date().toISOString()
    });
    
    success('Pedido enviado correctamente');
    
    // Limpiar formulario
    formData.value = {
      name: '',
      address: '',
      phone: '',
      paymentMethod: 'efectivo',
      special_instructions: ''
    };
    
  } catch (err) {
    console.error('❌ Error al procesar el pedido:', err);
    showError('Error al procesar el pedido: ' + (err.response?.data?.message || err.message));
  } finally {
    isSubmitting.value = false;
  }
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
  max-height: 90vh;
  overflow-y: auto;
}

h2 {
  color: var(--text-color);
  margin-top: 0;
  margin-bottom: 1.5rem;
}

.order-summary {
  background: rgba(255, 123, 0, 0.1);
  padding: 1rem;
  border-radius: 6px;
  margin-bottom: 2rem;
}

.order-summary h3 {
  margin-top: 0;
  color: var(--text-color);
}

.order-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.order-total {
  border-top: 1px solid var(--border-color);
  padding-top: 0.5rem;
  margin-top: 1rem;
  color: var(--text-color);
}

.form-group {
  margin-bottom: 1.25rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

input, select, textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background-color: var(--card-bg);
  color: var(--text-color);
  box-sizing: border-box;
}

textarea {
  resize: vertical;
}

.error-message {
  color: #ff4d4f;
  margin-top: 0.25rem;
  font-size: 0.875rem;
}

.actions {
  display: flex;
  justify-content: space-between;
  margin-top: 1.5rem;
  gap: 1rem;
}

.cancel-btn {
  flex: 1;
  padding: 0.75rem 1.5rem;
  background: none;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  color: var(--text-color);
  cursor: pointer;
}

.submit-btn {
  flex: 2;
  padding: 0.75rem 1.5rem;
  background: var(--button-primary);
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  position: relative;
  font-weight: 500;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loader {
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
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