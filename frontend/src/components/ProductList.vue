<template>
  <div>
    <!-- Mostrar estado de carga -->
    <div v-if="loading" class="loading-state">
      🔄 Cargando productos...
    </div>
    
    <!-- Mostrar mensaje de error si hay alguno -->
    <div v-if="error" class="error-notice">
      ⚠️ {{ error }}
    </div>
    
    <!-- Contenido principal -->
    <div v-if="!loading">
      <FoodCategories @change-category="changeCategory" />
      
      <div class="products-grid">
        <ProductCard 
          v-for="product in filteredProducts" 
          :key="product.id" 
          :product="product"
          @add-to-cart="handleAddToCart"
        />
      </div>
      
      <!-- Mensaje si no hay productos -->
      <div v-if="filteredProducts.length === 0" class="no-products">
        😔 No se encontraron productos en esta categoría
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import FoodCategories from './FoodCategories.vue';
import ProductCard from './ProductCard.vue';
import api from '../services/api';

const activeCategory = ref('hamburguesas');
const products = ref([]);
const loading = ref(true);
const error = ref('');
const emit = defineEmits(['add-to-cart']);

const props = defineProps({
  searchQuery: {
    type: String,
    default: ''
  }
});

// Productos de respaldo (fallback) para cuando la API no funcione
const fallbackProducts = [
  {
    id: 1,
    name: 'Hamburguesa Clásica',
    description: 'Carne de res jugosa, lechuga fresca, tomate, cebolla, pepinillos y nuestra salsa especial',
    price: 15.99,
    image: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    category: 'hamburguesas',
    rating: 4.7,
    time: '10-15 min',
    servings: '1 persona'
  },
  {
    id: 2,
    name: 'Pizza Margherita',
    description: 'Salsa de tomate casera, mozzarella fresca, albahaca y aceite de oliva virgen extra',
    price: 18.99,
    image: 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    category: 'pizzas',
    rating: 4.8,
    time: '15-20 min',
    servings: '2 personas'
  },
  {
    id: 3,
    name: 'Pizza Pepperoni',
    description: 'Salsa de tomate, mozzarella, pepperoni premium y orégano',
    price: 21.99,
    image: 'https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    category: 'pizzas',
    rating: 4.6,
    time: '15-20 min',
    servings: '2 personas'
  },
  {
    id: 4,
    name: 'Ensalada César',
    description: 'Lechuga romana fresca, crutones dorados, queso parmesano y aderezo César casero',
    price: 12.99,
    image: 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    category: 'ensaladas',
    rating: 4.5,
    time: '5-10 min',
    servings: '1 persona'
  },
  {
    id: 5,
    name: 'Ensalada Griega',
    description: 'Tomate, pepino, cebolla roja, aceitunas, queso feta y aceite de oliva',
    price: 14.99,
    image: 'https://images.unsplash.com/photo-1540420773420-3366772f4999?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    category: 'ensaladas',
    rating: 4.4,
    time: '5-10 min',
    servings: '1 persona'
  },
  {
    id: 6,
    name: 'Espaguetis Bolognesa',
    description: 'Pasta fresca con salsa de carne tradicional, tomate y hierbas aromáticas',
    price: 16.99,
    image: 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    category: 'pastas',
    rating: 4.7,
    time: '15-20 min',
    servings: '1 persona'
  },
  {
    id: 7,
    name: 'Fetuccini Alfredo',
    description: 'Pasta fetuccini con cremosa salsa alfredo y queso parmesano',
    price: 18.99,
    image: 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    category: 'pastas',
    rating: 4.6,
    time: '15-20 min',
    servings: '1 persona'
  },
  {
    id: 8,
    name: 'Hamburguesa BBQ',
    description: 'Carne de res con salsa BBQ, aros de cebolla, bacon y queso cheddar',
    price: 18.99,
    image: 'https://images.unsplash.com/photo-1572802419224-296b0aeee0d9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    category: 'hamburguesas',
    rating: 4.8,
    time: '12-18 min',
    servings: '1 persona'
  }
];

// Cargar productos de la API o usar fallback
onMounted(async () => {
  try {
    console.log('🔄 Intentando cargar productos desde la API...');
    const response = await api.getProducts();
    console.log('✅ Respuesta de la API:', response.data);
    
    if (response.data && response.data.length > 0) {
      products.value = response.data;
      console.log('✅ Productos cargados desde la API');
    } else {
      console.log('⚠️ API devolvió datos vacíos, usando productos de fallback');
      products.value = fallbackProducts;
      error.value = 'Mostrando productos de demostración (API sin datos)';
    }
  } catch (apiError) {
    console.error('❌ Error al cargar productos de la API:', apiError);
    console.log('🔄 Usando productos de fallback...');
    products.value = fallbackProducts;
    error.value = 'Sin conexión al servidor - Mostrando datos de prueba';
  } finally {
    loading.value = false;
    console.log('📊 Total de productos cargados:', products.value.length);
  }
});

const filteredProducts = computed(() => {
  let filtered = products.value;
  
  // Filtrar por categoría si hay una seleccionada
  if (activeCategory.value) {
    filtered = filtered.filter(product => product.category === activeCategory.value);
  }
  
  // Si hay un término de búsqueda, filtrar por nombre o descripción
  if (props.searchQuery) {
    const searchTerm = props.searchQuery.toLowerCase();
    filtered = filtered.filter(product => 
      product.name.toLowerCase().includes(searchTerm) || 
      product.description.toLowerCase().includes(searchTerm)
    );
  }
  
  return filtered;
});

function changeCategory(category) {
  activeCategory.value = category;
  console.log('📂 Categoría cambiada a:', category);
}

function handleAddToCart(product) {
  emit('add-to-cart', product);
  console.log('🛒 Producto añadido al carrito:', product.name);
}
</script>

<style scoped>
.loading-state {
  text-align: center;
  padding: 3rem;
  color: var(--text-color);
  font-size: 1.2rem;
}

.error-notice {
  background-color: #fff3cd;
  border: 1px solid #ffeaa7;
  color: #856404;
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
  text-align: center;
}

.no-products {
  text-align: center;
  padding: 3rem;
  color: var(--text-color);
  opacity: 0.7;
  font-size: 1.2rem;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
  padding: 1rem 0;
}

@media (max-width: 768px) {
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
  }
}

/* Estilos para el componente ProductCard */
.product-card {
  display: flex;
  flex-direction: column;
  background-color: var(--card-bg);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--box-shadow);
  height: 100%;
  transition: transform 0.3s, box-shadow 0.3s;
  border: 1px solid var(--border-color);
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

/* Contenedor limitado para la imagen */
.product-image-container {
  width: 100%;
  height: 180px; /* Altura fija más pequeña */
  overflow: hidden;
  position: relative;
  background-color: rgba(128, 128, 128, 0.1);
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Mantiene la proporción y cubre el área */
  transition: transform 0.3s;
}

/* Efecto hover opcional para la imagen */
.product-image-container:hover .product-image {
  transform: scale(1.05);
}

.placeholder-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.placeholder-image span {
  font-size: 3rem;
  color: var(--border-color);
}

.product-info {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.product-name {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
  color: var(--text-color);
}

.product-price {
  font-size: 1.25rem;
  font-weight: 600;
  color: #ff6b00;
}

.product-description {
  color: var(--text-color);
  opacity: 0.8;
  margin-bottom: 1rem;
  line-height: 1.4;
  /* Aseguramos que el texto no se corte */
  display: -webkit-box;
  -webkit-line-clamp: 3; /* Limita a 3 líneas */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  align-items: center;
}

.rating, .time, .servings {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: var(--text-color);
  opacity: 0.7;
  font-size: 0.9rem;
}

.add-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: var(--button-primary);
  color: white;
  border: none;
  border-radius: 6px;
  padding: 0.75rem 1.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  margin-top: auto;
  width: fit-content;
}

.add-button:hover {
  transform: translateY(-2px);
  box-shadow: var(--box-shadow);
  background: var(--button-hover);
}
</style>