<!-- filepath: frontend/src/views/RestaurantMenu.vue -->
<template>
  <div class="restaurant-menu-container">
    <div v-if="loading" class="loading-state">
      🔄 Cargando restaurante...
    </div>
    
    <div v-else-if="restaurant" class="restaurant-detail">
      <!-- Header del restaurante -->
      <div class="restaurant-header">
        <div class="restaurant-cover">
          <img 
            :src="restaurant.cover_image || '/default-restaurant.jpg'" 
            :alt="restaurant.name"
            class="cover-image"
          >
          <div class="restaurant-info-overlay">
            <h1 class="restaurant-name">{{ restaurant.name }}</h1>
            <p class="restaurant-description">{{ restaurant.description }}</p>
            <div class="restaurant-meta">
              <div class="meta-item">
                <span class="icon">⭐</span>
                <span>{{ restaurant.rating }} ({{ restaurant.total_reviews }} reseñas)</span>
              </div>
              <div class="meta-item">
                <span class="icon">🚚</span>
                <span>{{ restaurant.delivery_time_min }}-{{ restaurant.delivery_time_max }} min</span>
              </div>
              <div class="meta-item">
                <span class="icon">💰</span>
                <span>Envío: ${{ restaurant.delivery_fee }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Menú del restaurante -->
      <div class="menu-section">
        <div class="menu-header">
          <h2>Menú</h2>
          <div class="search-menu">
            <input 
              v-model="searchQuery" 
              placeholder="Buscar en el menú..."
              class="menu-search-input"
            >
          </div>
        </div>

        <div class="menu-content">
          <div v-if="filteredProducts.length > 0" class="products-grid">
            <ProductCard 
              v-for="product in filteredProducts" 
              :key="product.id" 
              :product="product"
              @add-to-cart="handleAddToCart"
            />
          </div>

          <div v-else class="no-products">
            <p>No se encontraron productos para este restaurante</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="error-state">
      <h2>Restaurante no encontrado</h2>
      <button @click="$router.push('/restaurants')" class="back-btn">
        Volver a restaurantes
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ProductCard from '../components/ProductCard.vue';
import api from '../services/api';

const route = useRoute();
const router = useRouter();
const { addToCart } = inject('cart');

const restaurant = ref(null);
const products = ref([]);
const loading = ref(true);
const searchQuery = ref('');

// Productos de fallback para el restaurante
const fallbackProducts = [
  {
    id: 1,
    name: 'Pizza Margherita',
    description: 'Salsa de tomate, mozzarella fresca, albahaca',
    price: 18.99,
    image: 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    restaurant_id: parseInt(route.params.id),
    rating: 4.8,
    time: '15-20 min'
  },
  {
    id: 2,
    name: 'Pizza Pepperoni',
    description: 'Salsa de tomate, mozzarella, pepperoni premium',
    price: 21.99,
    image: 'https://images.unsplash.com/photo-1628840042765-356cda07504e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    restaurant_id: parseInt(route.params.id),
    rating: 4.7,
    time: '15-20 min'
  },
  {
    id: 3,
    name: 'Ensalada César',
    description: 'Lechuga romana, crutones, parmesano, aderezo César',
    price: 12.99,
    image: 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
    restaurant_id: parseInt(route.params.id),
    rating: 4.5,
    time: '5-10 min'
  }
];

const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value;
  
  const query = searchQuery.value.toLowerCase();
  return products.value.filter(product => 
    product.name.toLowerCase().includes(query) ||
    product.description.toLowerCase().includes(query)
  );
});

onMounted(async () => {
  try {
    const restaurantId = route.params.id;
    console.log('🔄 Cargando restaurante ID:', restaurantId);
    
    // Cargar datos del restaurante
    try {
      const restaurantResponse = await api.getRestaurant(restaurantId);
      restaurant.value = restaurantResponse.data;
    } catch (error) {
      // Fallback para el restaurante
      const fallbackRestaurants = [
        {
          id: 1,
          name: 'Pizzería Italiana',
          description: 'Auténtica pizza italiana con ingredientes frescos',
          rating: 4.8,
          total_reviews: 156,
          delivery_time_min: 25,
          delivery_time_max: 35,
          delivery_fee: 3.50,
          cover_image: 'https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
        },
        {
          id: 2,
          name: 'Burger House',
          description: 'Las mejores hamburguesas gourmet de la ciudad',
          rating: 4.6,
          total_reviews: 89,
          delivery_time_min: 20,
          delivery_time_max: 30,
          delivery_fee: 2.99,
          cover_image: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
        }
      ];
      
      restaurant.value = fallbackRestaurants.find(r => r.id == restaurantId) || fallbackRestaurants[0];
    }
    
    // Cargar productos del restaurante
    try {
      const productsResponse = await api.getProductsByRestaurant(restaurantId);
      products.value = productsResponse.data;
    } catch (error) {
      console.log('🔄 Usando productos de fallback...');
      products.value = fallbackProducts;
    }
    
  } catch (error) {
    console.error('❌ Error al cargar restaurante:', error);
  } finally {
    loading.value = false;
  }
});

function handleAddToCart(product) {
  addToCart(product);
}
</script>

<style scoped>
.restaurant-menu-container {
  min-height: 100vh;
}

.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 50vh;
  color: var(--text-color);
}

.restaurant-header {
  position: relative;
  height: 400px;
  overflow: hidden;
}

.cover-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.restaurant-info-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(transparent, rgba(0,0,0,0.8));
  color: white;
  padding: 2rem;
}

.restaurant-name {
  font-size: 2.5rem;
  margin: 0 0 0.5rem 0;
  font-weight: bold;
}

.restaurant-description {
  font-size: 1.2rem;
  margin: 0 0 1rem 0;
  opacity: 0.9;
}

.restaurant-meta {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.meta-item .icon {
  font-size: 1.2rem;
}

.menu-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  gap: 1rem;
}

.menu-header h2 {
  color: var(--text-color);
  margin: 0;
}

.menu-search-input {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-bg);
  color: var(--text-color);
  width: 300px;
  max-width: 100%;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.no-products {
  text-align: center;
  padding: 3rem;
  color: var(--text-color);
  opacity: 0.7;
}

.back-btn {
  padding: 0.75rem 1.5rem;
  background: var(--button-primary);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

@media (max-width: 768px) {
  .restaurant-name {
    font-size: 2rem;
  }
  
  .restaurant-meta {
    gap: 1rem;
  }
  
  .menu-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .menu-search-input {
    width: 100%;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>