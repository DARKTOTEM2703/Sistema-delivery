<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ item ? '✏️ Editar Ítem' : '➕ Nuevo Ítem' }}</h2>
        <button @click="$emit('close')" class="close-btn">×</button>
      </div>
      
      <form @submit.prevent="saveItem" class="item-form">
        <div class="form-group">
          <label for="name">Nombre *</label>
          <input 
            id="name"
            v-model="formData.name"
            type="text"
            required
            placeholder="Ej: Harina de trigo"
          >
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="category">Categoría *</label>
            <select 
              id="category"
              v-model="formData.category"
              required
            >
              <option value="">Seleccionar categoría</option>
              <option value="Ingredientes">Ingredientes</option>
              <option value="Lácteos">Lácteos</option>
              <option value="Carnes">Carnes</option>
              <option value="Vegetales">Vegetales</option>
              <option value="Bebidas">Bebidas</option>
              <option value="Desechables">Desechables</option>
              <option value="Limpieza">Limpieza</option>
              <option value="Otros">Otros</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="unit">Unidad de medida *</label>
            <select 
              id="unit"
              v-model="formData.unit"
              required
            >
              <option value="">Seleccionar unidad</option>
              <option value="kg">Kilogramos (kg)</option>
              <option value="g">Gramos (g)</option>
              <option value="l">Litros (l)</option>
              <option value="ml">Mililitros (ml)</option>
              <option value="unidades">Unidades</option>
              <option value="cajas">Cajas</option>
              <option value="paquetes">Paquetes</option>
            </select>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="current_stock">Stock actual *</label>
            <input 
              id="current_stock"
              v-model.number="formData.current_stock"
              type="number"
              step="0.01"
              min="0"
              required
              placeholder="0.00"
            >
          </div>
          
          <div class="form-group">
            <label for="min_stock">Stock mínimo *</label>
            <input 
              id="min_stock"
              v-model.number="formData.min_stock"
              type="number"
              step="0.01"
              min="0"
              required
              placeholder="0.00"
            >
          </div>
        </div>
        
        <div class="form-actions">
          <button type="button" @click="$emit('close')" class="btn-secondary">
            Cancelar
          </button>
          <button type="submit" :disabled="loading" class="btn-primary">
            {{ loading ? 'Guardando...' : (item ? 'Actualizar' : 'Crear') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '@/services/api';

const props = defineProps({
  item: {
    type: Object,
    default: null
  },
  restaurantId: {
    type: [String, Number],
    required: true
  }
});

const emit = defineEmits(['close', 'saved']);

const loading = ref(false);

const formData = reactive({
  name: '',
  category: '',
  current_stock: 0,
  unit: '',
  min_stock: 0
});

// Cargar datos del ítem si estamos editando
onMounted(() => {
  if (props.item) {
    formData.name = props.item.name || '';
    formData.category = props.item.category || '';
    formData.current_stock = props.item.current_stock || 0;
    formData.unit = props.item.unit || '';
    formData.min_stock = props.item.min_stock || 0;
  }
});

async function saveItem() {
  loading.value = true;
  
  try {
    if (props.item) {
      // Actualizar item existente
      await api.put(`/inventory/${props.item.id}`, formData);
    } else {
      // Crear nuevo item
      await api.post(`/restaurants/${props.restaurantId}/inventory`, formData);
    }
    
    emit('saved');
    emit('close');
  } catch (error) {
    console.error('Error al guardar ítem:', error);
  } finally {
    loading.value = false;
  }
}
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  background: white;
  z-index: 1;
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

.item-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #4a5568;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-primary,
.btn-secondary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.btn-primary {
  background: #4299e1;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #3182ce;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #e2e8f0;
  color: #4a5568;
}

.btn-secondary:hover {
  background: #cbd5e0;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>