<!-- filepath: frontend/src/views/RestaurantList.vue -->
<template>
  <div class="restaurant-list-container">
    <div class="page-header">
      <h1>Restaurantes Disponibles</h1>
      <p>Descubre los mejores sabores cerca de ti</p>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="filters-section">
      <div class="search-filters">
        <div class="search-input-container">
          <input 
            v-model="searchQuery" 
            placeholder="Buscar restaurantes, comida, categoría..."
            class="search-input"
          >
          <button class="search-btn">🔍</button>
        </div>

        <select v-model="selectedCategory" class="filter-select">
          <option value="">Todas las categorías</option>
          <option v-for="category in categories" :key="category" :value="category">
            {{ category }}
          </option>
        </select>

        <select v-model="sortBy" class="filter-select">
          <option value="rating">Mejor calificados</option>
          <option value="delivery_time">Entrega más rápida</option>
          <option value="delivery_fee">Menor costo de envío</option>
          <option value="name">Nombre A-Z</option>
        </select>

        <div class="filter-chips">
          <button 
            class="filter-chip"
            :class="{ active: showOpenOnly }"
            @click="showOpenOnly = !showOpenOnly"
          >
            Solo abiertos
          </button>
          <button 
            class="filter-chip"
            :class="{ active: minRating > 0 }"
            @click="toggleRatingFilter"
          >
            Rating 4+
          </button>
        </div>
      </div>
    </div>

    <!-- Resultado de búsqueda -->
    <div v-if="searchQuery" class="search-results-info">
      Mostrando {{ filteredRestaurants.length }} restaurantes para "{{ searchQuery }}"
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Cargando restaurantes...</p>
    </div>

    <!-- Lista de restaurantes -->
    <div v-else-if="filteredRestaurants.length > 0" class="restaurants-grid">
      <RestaurantCard 
        v-for="restaurant in paginatedRestaurants" 
        :key="restaurant.id"
        :restaurant="restaurant"
        @select-restaurant="selectRestaurant"
        @view-menu="viewMenu"
        @toggle-favorite="toggleFavorite"
      />
    </div>

    <!-- Estado vacío -->
    <div v-else class="empty-state">
      <div class="empty-icon">🍽️</div>
      <h3>No se encontraron restaurantes</h3>
      <p>Intenta ajustar tus filtros o buscar en otra área</p>
      <button class="clear-filters-btn" @click="clearFilters">
        Limpiar filtros
      </button>
    </div>

    <!-- Paginación -->
    <div v-if="totalPages > 1" class="pagination">
      <button 
        class="pagination-btn"
        :disabled="currentPage === 1"
        @click="changePage(currentPage - 1)"
      >
        Anterior
      </button>
      
      <span class="pagination-info">
        Página {{ currentPage }} de {{ totalPages }}
      </span>
      
      <button 
        class="pagination-btn"
        :disabled="currentPage === totalPages"
        @click="changePage(currentPage + 1)"
      >
        Siguiente
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import RestaurantCard from '../components/RestaurantCard.vue';
import api from '../services/api';

const router = useRouter();

// Estado reactivo
const restaurants = ref([]);
const categories = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const selectedCategory = ref('');
const sortBy = ref('rating');
const showOpenOnly = ref(false);
const minRating = ref(0);
const currentPage = ref(1);
const itemsPerPage = 12;

// Datos de fallback
const fallbackRestaurants = [
  {
    id: 1,
    name: 'Pizzería Italiana',
    description: 'Auténtica pizza italiana con ingredientes frescos y masa tradicional',
    category: 'italiana',
    rating: 4.8,
    total_reviews: 156,
    delivery_time_min: 25,
    delivery_time_max: 35,
    delivery_fee: 3.50,
    minimum_order: 15.00,
    is_open_now: true,
    cover_image: 'https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 2,
    name: 'Burguer House',
    description: 'Las mejores hamburguesas gourmet de la ciudad con ingredientes premium',
    category: 'americana',
    rating: 4.6,
    total_reviews: 89,
    delivery_time_min: 20,
    delivery_time_max: 30,
    delivery_fee: 2.99,
    minimum_order: 12.00,
    is_open_now: true,
    cover_image: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 3,
    name: 'Sushi Zen',
    description: 'Sushi fresco y rollos especiales preparados por chefs japoneses',
    category: 'japonesa',
    rating: 4.9,
    total_reviews: 203,
    delivery_time_min: 30,
    delivery_time_max: 45,
    delivery_fee: 4.99,
    minimum_order: 20.00,
    is_open_now: false,
    cover_image: 'https://images.unsplash.com/photo-1553621042-f6e147245754?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 4,
    name: 'Tacos El Mariachi',
    description: 'Tacos mexicanos auténticos con recetas tradicionales',
    category: 'mexicana',
    rating: 4.7,
    total_reviews: 127,
    delivery_time_min: 15,
    delivery_time_max: 25,
    delivery_fee: 2.50,
    minimum_order: 10.00,
    is_open_now: true,
    cover_image: 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 5,
    name: 'Green Salads',
    description: 'Ensaladas frescas y saludables con ingredientes orgánicos',
    category: 'saludable',
    rating: 4.5,
    total_reviews: 74,
    delivery_time_min: 10,
    delivery_time_max: 20,
    delivery_fee: 1.99,
    minimum_order: 8.00,
    is_open_now: true,
    cover_image: 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 6,
    name: 'Pasta & More',
    description: 'Pasta artesanal italiana con salsas caseras',
    category: 'italiana',
    rating: 4.4,
    total_reviews: 91,
    delivery_time_min: 25,
    delivery_time_max: 35,
    delivery_fee: 3.99,
    minimum_order: 14.00,
    is_open_now: true,
    cover_image: 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  }
];

// Computed properties
const filteredRestaurants = computed(() => {
  let filtered = restaurants.value;
  
  // Filtrar por búsqueda
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(restaurant => 
      restaurant.name.toLowerCase().includes(query) ||
      restaurant.description.toLowerCase().includes(query) ||
      restaurant.category.toLowerCase().includes(query)
    );
  }
  
  // Filtrar por categoría
  if (selectedCategory.value) {
    filtered = filtered.filter(restaurant => 
      restaurant.category === selectedCategory.value
    );
  }
  
  // Filtrar solo abiertos
  if (showOpenOnly.value) {
    filtered = filtered.filter(restaurant => restaurant.is_open_now);
  }
  
  // Filtrar por rating
  if (minRating.value > 0) {
    filtered = filtered.filter(restaurant => restaurant.rating >= minRating.value);
  }
  
  // Ordenar
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'rating':
        return b.rating - a.rating;
      case 'delivery_time':
        return a.delivery_time_min - b.delivery_time_min;
      case 'delivery_fee':
        return a.delivery_fee - b.delivery_fee;
      case 'name':
        return a.name.localeCompare(b.name);
      default:
        return 0;
    }
  });
  
  return filtered;
});

const totalPages = computed(() => {
  return Math.ceil(filteredRestaurants.value.length / itemsPerPage);
});

const paginatedRestaurants = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredRestaurants.value.slice(start, end);
});

// Métodos
onMounted(async () => {
  try {
    console.log('🔄 Cargando restaurantes...');
    
    // Cargar restaurantes
    const restaurantsResponse = await api.getRestaurants();
    // ✅ ARREGLAR AQUÍ TAMBIÉN
    restaurants.value = restaurantsResponse.data.data || restaurantsResponse.data;
    
    // Cargar categorías
    try {
      const categoriesResponse = await api.getRestaurantCategories();
      categories.value = categoriesResponse.data;
    } catch (catError) {
      console.log('⚠️ Error cargando categorías, usando fallback');
      categories.value = [...new Set(restaurants.value.map(r => r.category))];
    }
    
    console.log('✅ Restaurantes cargados:', restaurants.value.length);
  } catch (error) {
    console.error('❌ Error al cargar restaurantes:', error);
    console.log('🔄 Usando datos de fallback...');
    
    // Usar datos de fallback
    restaurants.value = fallbackRestaurants;
    categories.value = [...new Set(fallbackRestaurants.map(r => r.category))];
  } finally {
    loading.value = false;
  }
});

// Watchers
watch([searchQuery, selectedCategory, sortBy, showOpenOnly, minRating], () => {
  currentPage.value = 1;
});

function selectRestaurant(restaurant) {
  router.push(`/restaurant/${restaurant.id}`);
}

function viewMenu(restaurant) {
  router.push(`/restaurant/${restaurant.id}`); // ✅ Ruta correcta
}

function toggleFavorite(restaurant) {
  console.log('Toggle favorite:', restaurant.name);
  // Implementar lógica de favoritos
}

function toggleRatingFilter() {
  minRating.value = minRating.value > 0 ? 0 : 4;
}

function clearFilters() {
  searchQuery.value = '';
  selectedCategory.value = '';
  sortBy.value = 'rating';
  showOpenOnly.value = false;
  minRating.value = 0;
  currentPage.value = 1;
}

function changePage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}
</script>

<style scoped>
.restaurant-list-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-header h1 {
  font-size: 2.5rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.page-header p {
  font-size: 1.1rem;
  color: var(--text-color);
  opacity: 0.7;
}

.filters-section {
  background: var(--card-bg);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--box-shadow);
  border: 1px solid var(--border-color);
}

.search-filters {
  display: grid;
  grid-template-columns: 1fr auto auto;
  gap: 1rem;
  align-items: start;
}

.search-input-container {
  position: relative;
}

.search-input {
  width: 100%;
  padding: 0.75rem 3rem 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-bg);
  color: var(--text-color);
  font-size: 1rem;
}

.search-btn {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.5rem;
}

.filter-select {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-bg);
  color: var(--text-color);
  min-width: 150px;
}

.filter-chips {
  display: flex;
  gap: 0.5rem;
  grid-column: 1 / -1;
  margin-top: 1rem;
}

.filter-chip {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 20px;
  background: var(--card-bg);
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.filter-chip:hover {
  background: rgba(255, 123, 0, 0.1);
  border-color: #ff7b00;
}

.filter-chip.active {
  background: var(--button-primary);
  color: white;
  border-color: transparent;
}

.search-results-info {
  margin-bottom: 1rem;
  color: var(--text-color);
  opacity: 0.7;
  font-size: 0.9rem;
}

.loading-container {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-color);
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 3px solid var(--border-color);
  border-top: 3px solid var(--button-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.restaurants-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-color);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.empty-state p {
  opacity: 0.7;
  margin-bottom: 2rem;
}

.clear-filters-btn {
  background: var(--button-primary);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.pagination-btn {
  padding: 0.75rem 1.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-bg);
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background: var(--button-primary);
  color: white;
  border-color: transparent;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-info {
  color: var(--text-color);
  font-weight: 500;
}

@media (max-width: 768px) {
  .search-filters {
    grid-template-columns: 1fr;
  }
  
  .filter-chips {
    grid-column: 1;
  }
  
  .restaurants-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .pagination {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>