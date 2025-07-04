<!-- filepath: c:\xampp\htdocs\Sistema-delivery\frontend\src\components\CloseShiftModal.vue -->
<template>
  <Teleport to="body">
    <div class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-content">
        <div class="modal-header">
          <h2>🔒 Cerrar Turno de Caja</h2>
          <button @click="$emit('close')" class="close-btn">×</button>
        </div>
        
        <div class="modal-body">
          <div class="shift-summary">
            <h3>Resumen del Turno</h3>
            <div class="summary-item">
              <span>Dinero inicial:</span>
              <span>${{ formatPrice(shift?.opening_cash || 0) }}</span>
            </div>
            <div class="summary-item">
              <span>Ventas estimadas:</span>
              <span>${{ formatPrice(estimatedSales) }}</span>
            </div>
            <div class="summary-item expected">
              <span>Dinero esperado:</span>
              <span>${{ formatPrice(expectedCash) }}</span>
            </div>
          </div>
          
          <div class="form-group">
            <label for="closingCash">💰 Dinero contado en caja:</label>
            <input 
              type="number" 
              id="closingCash"
              v-model.number="closingCash"
              step="0.01"
              min="0"
              placeholder="0.00"
              required
              ref="cashInput"
              @keydown.enter="confirmClose"
              @keydown.esc="$emit('close')"
            >
          </div>
          
          <div v-if="difference !== 0" class="difference-alert" :class="{ surplus: difference > 0 }">
            <div class="difference-icon">
              {{ difference > 0 ? '📈' : '📉' }}
            </div>
            <div class="difference-text">
              <strong>{{ difference > 0 ? 'Sobrante' : 'Faltante' }}:</strong>
              ${{ formatPrice(Math.abs(difference)) }}
            </div>
          </div>
          
          <div class="form-group">
            <label for="notes">📝 Notas de cierre:</label>
            <textarea 
              id="notes"
              v-model="notes"
              placeholder="Observaciones, explicación de diferencias..."
              rows="3"
              @keydown.esc="$emit('close')"
            ></textarea>
          </div>
        </div>
        
        <div class="modal-footer">
          <button @click="$emit('close')" class="btn-secondary">
            Cancelar
          </button>
          <button @click="confirmClose" class="btn-primary">
            🔒 Cerrar Turno
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  shift: Object
})

const emit = defineEmits(['close', 'confirm'])

const closingCash = ref(0)
const notes = ref('')
const estimatedSales = ref(250.00)
const cashInput = ref(null)

const expectedCash = computed(() => {
  return (props.shift?.opening_cash || 0) + estimatedSales.value
})

const difference = computed(() => {
  return closingCash.value - expectedCash.value
})

function formatPrice(price) {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return isNaN(numPrice) ? '0.00' : numPrice.toFixed(2)
}

function confirmClose() {
  emit('confirm', {
    closingCash: closingCash.value,
    notes: notes.value
  })
}

function handleEscape(event) {
  if (event.key === 'Escape') {
    emit('close')
  }
}

function preventBodyScroll() {
  document.body.style.overflow = 'hidden'
  document.body.style.paddingRight = '0px'
}

function restoreBodyScroll() {
  document.body.style.overflow = ''
  document.body.style.paddingRight = ''
}

onMounted(() => {
  preventBodyScroll()
  document.addEventListener('keydown', handleEscape)
  
  nextTick(() => {
    cashInput.value?.focus()
  })
})

onUnmounted(() => {
  restoreBodyScroll()
  document.removeEventListener('keydown', handleEscape)
})
</script>

<style scoped>
/* ✅ USAR EL MISMO PATRÓN DE PANTALLA COMPLETA */
.modal-overlay {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  background: rgba(0, 0, 0, 0.75) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  z-index: 999999 !important;
  backdrop-filter: blur(3px);
  padding: 1rem;
  box-sizing: border-box;
  margin: 0 !important;
}

.modal-content {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 20px 25px -5px rgba(0, 0, 0, 0.1);
  position: relative;
  margin: auto;
}

/* Resto de estilos iguales al anterior... */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  background: white;
  border-radius: 16px 16px 0 0;
  position: sticky;
  top: 0;
  z-index: 10;
}

.modal-header h2 {
  margin: 0;
  color: #2d3748;
  font-size: 1.25rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #a0aec0;
  padding: 4px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  line-height: 1;
}

.close-btn:hover {
  background: #f7fafc;
  color: #4a5568;
  transform: scale(1.1);
}

.modal-body {
  padding: 1.5rem;
}

.shift-summary {
  background: #f7fafc;
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  border: 1px solid #e2e8f0;
}

.shift-summary h3 {
  margin: 0 0 1rem 0;
  color: #4a5568;
  font-size: 1.1rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
  color: #4a5568;
  font-size: 0.95rem;
}

.summary-item.expected {
  font-weight: bold;
  border-top: 2px solid #e2e8f0;
  padding-top: 0.75rem;
  margin-top: 0.75rem;
  color: #2d3748;
  font-size: 1.1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #4a5568;
  font-size: 0.95rem;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s ease;
  box-sizing: border-box;
  font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #f56565;
  box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.1);
}

.form-group input[type="number"] {
  text-align: center;
  font-size: 1.3rem;
  font-weight: 500;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.difference-alert {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  background: #fed7d7;
  color: #c53030;
  border: 1px solid #feb2b2;
}

.difference-alert.surplus {
  background: #c6f6d5;
  color: #2f855a;
  border-color: #9ae6b4;
}

.difference-icon {
  font-size: 1.5rem;
}

.difference-text {
  flex: 1;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 0 0 16px 16px;
}

.btn-primary,
.btn-secondary {
  flex: 1;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.btn-primary {
  background: #f56565;
  color: white;
}

.btn-primary:hover {
  background: #e53e3e;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(245, 101, 101, 0.4);
}

.btn-secondary {
  background: #edf2f7;
  color: #4a5568;
  border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #e2e8f0;
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 640px) {
  .modal-overlay {
    padding: 0.5rem !important;
  }
  
  .modal-content {
    max-height: 95vh;
  }
  
  .modal-footer {
    flex-direction: column;
  }
}

/* Animaciones */
@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes overlayFadeIn {
  from {
    opacity: 0;
    backdrop-filter: blur(0px);
  }
  to {
    opacity: 1;
    backdrop-filter: blur(3px);
  }
}

.modal-overlay {
  animation: overlayFadeIn 0.3s ease-out;
}

.modal-content {
  animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Evitar bugs visuales */
* {
  box-sizing: border-box;
}
</style>