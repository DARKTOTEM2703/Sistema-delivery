<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ product ? 'Editar Producto' : 'Agregar Producto' }}</h2>
        <button @click="$emit('close')" class="close-btn">×</button>
      </div>
      
      <form @submit.prevent="saveProduct" class="product-form">
        <div class="form-group">
          <label for="name">Nombre del producto *</label>
          <input 
            type="text" 
            id="name" 
            v-model="formData.name" 
            required 
            placeholder="Ej: Pizza Margherita"
          >
        </div>
        
        <div class="form-group">
          <label for="description">Descripción *</label>
          <textarea 
            id="description" 
            v-model="formData.description" 
            required 
            placeholder="Describe el producto..."
            rows="3"
          ></textarea>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="price">Precio *</label>
            <input 
              type="number" 
              id="price" 
              v-model.number="formData.price" 
              required 
              step="0.01"
              min="0"
              placeholder="0.00"
            >
          </div>
          
          <div class="form-group">
            <label for="category">Categoría *</label>
            <select id="category" v-model="formData.category" required>
              <option value="">Selecciona categoría</option>
              <option value="hamburguesas">🍔 Hamburguesas</option>
              <option value="pizzas">🍕 Pizzas</option>
              <option value="ensaladas">🥗 Ensaladas</option>
              <option value="pastas">🍝 Pastas</option>
              <option value="bebidas">🥤 Bebidas</option>
              <option value="postres">🍰 Postres</option>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label for="image">URL de imagen</label>
          <input 
            type="url" 
            id="image" 
            v-model="formData.image" 
            placeholder="https://ejemplo.com/imagen.jpg"
          >
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="prep_time">Tiempo de preparación (min)</label>
            <input 
              type="number" 
              id="prep_time" 
              v-model.number="formData.prep_time" 
              min="1"
              placeholder="15"
            >
          </div>
          
          <div class="form-group">
            <label for="servings">Porciones</label>
            <input 
              type="text" 
              id="servings" 
              v-model="formData.servings" 
              placeholder="1-2 personas"
            >
          </div>
        </div>
        
        <div class="form-group">
          <label class="checkbox-label">
            <input 
              type="checkbox" 
              v-model="formData.is_available"
            >
            Producto disponible
          </label>
        </div>
        
        <div v-if="error" class="error-message">
          {{ error }}
        </div>
        
        <div class="form-actions">
          <button type="button" @click="$emit('close')" class="btn-secondary">
            Cancelar
          </button>
          <button type="submit" :disabled="loading" class="btn-primary">
            {{ loading ? 'Guardando...' : (product ? 'Actualizar' : 'Crear') }}
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
  product: {
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
const error = ref('');

const formData = reactive({
  name: '',
  description: '',
  price: 0,
  category: '',
  image: '',
  prep_time: 15,
  servings: '1 persona',
  is_available: true
});

// Cargar datos del producto si estamos editando
onMounted(() => {
  if (props.product) {
    Object.assign(formData, {
      name: props.product.name || '',
      description: props.product.description || '',
      price: props.product.price || 0,
      category: props.product.category || '',
      image: props.product.image || '',
      prep_time: props.product.prep_time || 15,
      servings: props.product.servings || '1 persona',
      is_available: props.product.is_available !== false
    });
  }
});

async function saveProduct() {
  if (!validateForm()) return;
  
  loading.value = true;
  error.value = '';
  
  try {
    const productData = {
      ...formData,
      restaurant_id: props.restaurantId
    };
    
    if (props.product) {
      // Actualizar producto existente
      await api.put(`/products/${props.product.id}`, productData);
    } else {
      // Crear nuevo producto
      await api.post('/products', productData);
    }
    
    emit('saved');
  } catch (err) {
    console.error('Error guardando producto:', err);
    error.value = err.response?.data?.message || 'Error al guardar el producto';
  } finally {
    loading.value = false;
  }
}

function validateForm() {
  if (!formData.name.trim()) {
    error.value = 'El nombre es requerido';
    return false;
  }
  
  if (!formData.description.trim()) {
    error.value = 'La descripción es requerida';
    return false;
  }
  
  if (formData.price <= 0) {
    error.value = 'El precio debe ser mayor a 0';
    return false;
  }
  
  if (!formData.category) {
    error.value = 'La categoría es requerida';
    return false;
  }
  
  return true;
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: var(--card-bg);
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
  margin: 0;
  color: var(--text-color);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--text-color);
}

.product-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
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
  color: var(--text-color);
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-bg);
  color: var(--text-color);
  font-size: 1rem;
}

.checkbox-label {
  display: flex !important;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: auto !important;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 0.75rem;
  border-radius: 6px;
  margin-bottom: 1rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
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
  background: var(--button-primary);
  color: white;
}

.btn-secondary {
  background: var(--border-color);
  color: var(--text-color);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Media queries mejoradas para ProductModal */
@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .modal-content {
    width: 95%;
    max-height: 95vh;
    margin: 1rem;
  }
  
  .modal-header {
    padding: 1rem;
  }
  
  .product-form {
    padding: 1rem;
  }
  
  .form-actions {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .btn-primary,
  .btn-secondary {
    width: 100%;
    padding: 0.875rem;
  }
}

@media (max-width: 480px) {
  .modal-header h2 {
    font-size: 1.2rem;
  }
  
  .form-group label {
    font-size: 0.9rem;
  }
}
</style>