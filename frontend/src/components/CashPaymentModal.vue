<!-- filepath: c:\xampp\htdocs\Sistema-delivery\frontend\src\components\CashPaymentModal.vue -->
<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>💵 Pago en Efectivo</h2>
        <button @click="$emit('close')" class="close-btn">×</button>
      </div>
      
      <div class="modal-body">
        <div class="total-display">
          <h3>Total a cobrar:</h3>
          <div class="total-amount">${{ formatPrice(total) }}</div>
        </div>
        
        <div class="cash-input">
          <label for="cashReceived">Efectivo recibido:</label>
          <input 
            type="number" 
            id="cashReceived"
            v-model.number="cashReceived"
            @input="calculateChange"
            step="0.01"
            min="0"
            placeholder="0.00"
            ref="cashInput"
          >
        </div>
        
        <div class="change-display" :class="{ error: change < 0 }">
          <span>Cambio:</span>
          <span class="change-amount">
            {{ change < 0 ? 'Falta: ' : '' }}${{ formatPrice(Math.abs(change)) }}
          </span>
        </div>
        
        <!-- Botones de efectivo rápido -->
        <div class="quick-cash">
          <h4>Efectivo exacto:</h4>
          <div class="quick-buttons">
            <button 
              v-for="amount in quickAmounts"
              :key="amount"
              @click="setQuickAmount(amount)"
              class="quick-btn"
            >
              ${{ amount }}
            </button>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button @click="$emit('close')" class="btn-secondary">
          Cancelar
        </button>
        <button 
          @click="confirmPayment" 
          :disabled="!canConfirm"
          class="btn-primary"
        >
          {{ change < 0 ? 'Falta dinero' : 'Confirmar Pago' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'

const props = defineProps({
  total: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['close', 'confirm'])

const cashReceived = ref(0)
const cashInput = ref(null)

const change = computed(() => {
  return cashReceived.value - props.total
})

const canConfirm = computed(() => {
  return cashReceived.value >= props.total
})

const quickAmounts = computed(() => {
  const total = props.total
  const amounts = []
  
  // Efectivo exacto
  amounts.push(Math.ceil(total))
  
  // Múltiplos de 10, 20, 50, 100
  const roundedUp = Math.ceil(total / 10) * 10
  if (roundedUp > Math.ceil(total)) amounts.push(roundedUp)
  
  const twenties = Math.ceil(total / 20) * 20
  if (twenties > roundedUp) amounts.push(twenties)
  
  const fifties = Math.ceil(total / 50) * 50
  if (fifties > twenties) amounts.push(fifties)
  
  const hundreds = Math.ceil(total / 100) * 100
  if (hundreds > fifties && hundreds <= total + 100) amounts.push(hundreds)
  
  return [...new Set(amounts)].slice(0, 6)
})

function calculateChange() {
  // El computed ya maneja esto
}

function setQuickAmount(amount) {
  cashReceived.value = amount
}

function confirmPayment() {
  if (canConfirm.value) {
    emit('confirm', {
      cashReceived: cashReceived.value,
      change: change.value
    })
  }
}

function formatPrice(price) {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return isNaN(numPrice) ? '0.00' : numPrice.toFixed(2)
}

onMounted(() => {
  nextTick(() => {
    cashInput.value?.focus()
  })
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  margin: 0;
  color: #2d3748;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #a0aec0;
}

.modal-body {
  padding: 1.5rem;
}

.total-display {
  text-align: center;
  margin-bottom: 2rem;
  padding: 1rem;
  background: #f7fafc;
  border-radius: 8px;
}

.total-display h3 {
  margin: 0 0 0.5rem 0;
  color: #4a5568;
}

.total-amount {
  font-size: 2rem;
  font-weight: bold;
  color: #2d3748;
}

.cash-input {
  margin-bottom: 1rem;
}

.cash-input label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #4a5568;
}

.cash-input input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1.2rem;
  text-align: center;
}

.cash-input input:focus {
  outline: none;
  border-color: #4299e1;
}

.change-display {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f0fff4;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 1.1rem;
  font-weight: 500;
}

.change-display.error {
  background: #fed7d7;
  color: #c53030;
}

.change-amount {
  font-size: 1.3rem;
  font-weight: bold;
}

.quick-cash h4 {
  margin: 0 0 1rem 0;
  color: #4a5568;
}

.quick-buttons {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}

.quick-btn {
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.quick-btn:hover {
  background: #edf2f7;
  border-color: #cbd5e0;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.btn-primary,
.btn-secondary {
  flex: 1;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-primary {
  background: #48bb78;
  color: white;
}

.btn-primary:disabled {
  background: #cbd5e0;
  color: #a0aec0;
  cursor: not-allowed;
}

.btn-secondary {
  background: #edf2f7;
  color: #4a5568;
}

@media (max-width: 640px) {
  .quick-buttons {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .modal-footer {
    flex-direction: column;
  }
}
</style>