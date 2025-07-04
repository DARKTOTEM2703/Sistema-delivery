<!-- filepath: c:\xampp\htdocs\Sistema-delivery\frontend\src\components\OpenShiftModal.vue -->
<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>🔓 Abrir Turno de Caja</h2>
        <button @click="$emit('close')" class="close-btn">×</button>
      </div>
      
      <div class="modal-body">
        <div class="welcome-message">
          <div class="icon">🏪</div>
          <h3>Iniciar nuevo turno</h3>
          <p>Registra el dinero inicial para comenzar las ventas del día</p>
        </div>
        
        <div class="form-group">
          <label for="openingCash">💰 Dinero inicial en caja:</label>
          <input 
            type="number" 
            id="openingCash"
            v-model.number="openingCash"
            step="0.01"
            min="0"
            placeholder="0.00"
            required
            ref="cashInput"
          >
        </div>
        
        <div class="form-group">
          <label for="notes">📝 Notas de apertura:</label>
          <textarea 
            id="notes"
            v-model="notes"
            placeholder="Observaciones opcionales sobre el inicio del turno..."
            rows="3"
          ></textarea>
        </div>
      </div>
      
      <div class="modal-footer">
        <button @click="$emit('close')" class="btn-secondary">
          Cancelar
        </button>
        <button @click="confirmOpen" :disabled="!canOpen" class="btn-primary">
          🚀 Abrir Turno
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'

const emit = defineEmits(['close', 'confirm'])

const openingCash = ref(0)
const notes = ref('')
const cashInput = ref(null)

const canOpen = computed(() => {
  return openingCash.value >= 0
})

function confirmOpen() {
  if (canOpen.value) {
    emit('confirm', {
      openingCash: openingCash.value,
      notes: notes.value
    })
  }
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
  max-width: 450px;
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
  padding: 0;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: #f7fafc;
  color: #4a5568;
}

.modal-body {
  padding: 1.5rem;
}

.welcome-message {
  text-align: center;
  padding: 1.5rem;
  background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
  color: white;
  border-radius: 12px;
  margin-bottom: 1.5rem;
}

.welcome-message .icon {
  font-size: 3rem;
  margin-bottom: 0.5rem;
}

.welcome-message h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.3rem;
}

.welcome-message p {
  margin: 0;
  opacity: 0.9;
  font-size: 0.9rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #4a5568;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #48bb78;
}

.form-group input[type="number"] {
  text-align: center;
  font-size: 1.3rem;
  font-weight: 500;
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
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 1rem;
  transition: all 0.2s;
}

.btn-primary {
  background: #48bb78;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #38a169;
  transform: translateY(-1px);
}

.btn-primary:disabled {
  background: #cbd5e0;
  color: #a0aec0;
  cursor: not-allowed;
  transform: none;
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
  .modal-content {
    width: 95%;
    margin: 1rem;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 1rem;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .welcome-message {
    padding: 1rem;
  }
}

/* Animaciones */
@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-overlay {
  animation: modalFadeIn 0.2s ease-out;
}

.modal-content {
  animation: modalFadeIn 0.3s ease-out;
}
</style>