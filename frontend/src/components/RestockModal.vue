<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>🔄 Reabastecimiento de Inventario</h2>
        <button @click="$emit('close')" class="close-btn">×</button>
      </div>
      
      <div class="modal-body">
        <div class="item-info">
          <h3>{{ item?.name }}</h3>
          <div class="item-details">
            <p><strong>Categoría:</strong> {{ item?.category }}</p>
            <p><strong>Stock actual:</strong> {{ item?.current_stock }} {{ item?.unit }}</p>
            <p><strong>Stock mínimo:</strong> {{ item?.min_stock }} {{ item?.unit }}</p>
          </div>
        </div>
        
        <div class="form-group">
          <label for="quantity">Cantidad a añadir:</label>
          <input 
            type="number" 
            id="quantity" 
            v-model.number="quantity" 
            step="0.01" 
            min="0.01"
            placeholder="0.00"
            required
            ref="quantityInput"
          >
          <span class="unit">{{ item?.unit }}</span>
        </div>
        
        <div class="form-group">
          <label for="notes">Notas:</label>
          <textarea 
            id="notes"
            v-model="notes"
            placeholder="Detalles sobre el reabastecimiento..."
            rows="3"
          ></textarea>
        </div>
      </div>
      
      <div class="modal-footer">
        <button @click="$emit('close')" class="btn-secondary">
          Cancelar
        </button>
        <button @click="handleRestock" :disabled="!canRestock" class="btn-primary">
          Confirmar Reabastecimiento
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import api from '@/services/api';

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'restocked']);

const quantity = ref(0);
const notes = ref('');
const quantityInput = ref(null);

const canRestock = computed(() => {
  return quantity.value > 0;
});

async function handleRestock() {
  if (!canRestock.value) return;
  
  try {
    const response = await api.post(`/inventory/${props.item.id}/restock`, {
      quantity: quantity.value,
      notes: notes.value
    });
    
    emit('restocked', response.data);
    emit('close');
  } catch (error) {
    console.error('Error al reabastecer:', error);
  }
}

onMounted(() => {
  nextTick(() => {
    quantityInput.value?.focus();
  });
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
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

.item-info {
  margin-bottom: 1.5rem;
}

.item-info h3 {
  margin: 0 0 0.5rem 0;
  color: #2d3748;
}

.item-details {
  background: #f7fafc;
  border-radius: 8px;
  padding: 1rem;
}

.item-details p {
  margin: 0.5rem 0;
  color: #4a5568;
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
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
}

.unit {
  margin-left: 0.5rem;
  color: #718096;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.btn-primary,
.btn-secondary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-primary {
  background: #4299e1;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #3182ce;
}

.btn-primary:disabled {
  background: #a0aec0;
  cursor: not-allowed;
}

.btn-secondary {
  background: #e2e8f0;
  color: #4a5568;
}

.btn-secondary:hover {
  background: #cbd5e0;
}
</style>