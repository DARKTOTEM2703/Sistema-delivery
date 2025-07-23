<!-- filepath: frontend/src/views/Home.vue -->
<template>
  <div class="main-container">
    <!-- Banner principal -->
    <hero />
    
    <!-- Barra de búsqueda -->
    <div class="search-container">
      <div class="search-wrapper">
        <input 
          type="text" 
          placeholder="Buscar restaurantes..." 
          class="search-input" 
          v-model="searchQuery"
        />
        <button class="search-button" @click="handleSearch(searchQuery)">
          <span class="search-icon">🔍</span>
        </button>
      </div>
    </div>
    
    <!-- Estado de carga -->
    <div v-if="loading" class="loading-state">
      <p>Cargando restaurantes...</p>
    </div>
    
    <!-- Restaurantes mejor valorados -->
    <section v-if="!loading && topRatedRestaurants.length > 0" class="featured-section">
      <h2 class="section-title">Restaurantes Mejor Valorados</h2>
      <div class="restaurant-cards">
        <restaurant-card 
          v-for="restaurant in topRatedRestaurants" 
          :key="restaurant.id" 
          :restaurant="restaurant"
        />
      </div>
    </section>
    
    <!-- Mensaje si no hay restaurantes -->
    <div v-if="!loading && restaurants.length === 0" class="no-results">
      <p>No se encontraron restaurantes disponibles.</p>
    </div>
    
    <!-- Categorías de restaurantes -->
    <section v-if="!loading && restaurants.length > 0" class="categories-section">
      <h2 class="section-title">Explora por Categorías</h2>
      
      <div class="category-tabs">
        <button 
          v-for="category in categories" 
          :key="category.id"
          :class="['category-tab', activeCategory === category.id ? 'active' : '']"
          @click="setActiveCategory(category.id)"
        >
          <span class="category-icon">{{ category.icon }}</span>
          {{ category.name }}
        </button>
      </div>
      
      <div v-if="filteredRestaurants.length > 0" class="restaurant-grid">
        <restaurant-card 
          v-for="restaurant in filteredRestaurants" 
          :key="restaurant.id" 
          :restaurant="restaurant"
        />
      </div>
      
      <div v-else class="no-results">
        <p>No hay restaurantes disponibles en esta categoría.</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../services/api';
import hero from '../components/hero.vue';
import RestaurantCard from '../components/RestaurantCard.vue';

// Estado
const loading = ref(true);
const restaurants = ref([]);  // Inicializar como array vacío
const activeCategory = ref('all');

// Categorías disponibles
const categories = [
  { id: 'all', name: 'Todos', icon: '🍽️' },
  { id: 'pizza', name: 'Pizzas', icon: '🍕' },
  { id: 'burger', name: 'Hamburguesas', icon: '🍔' },
  { id: 'salad', name: 'Ensaladas', icon: '🥗' },
  { id: 'pasta', name: 'Pastas', icon: '🍝' },
  { id: 'asian', name: 'Asiática', icon: '🥢' },
  { id: 'mexican', name: 'Mexicana', icon: '🌮' },
  { id: 'dessert', name: 'Postres', icon: '🍰' }
];

// Obtener restaurantes al montar
onMounted(async () => {
  try {
    const response = await api.getRestaurants();
    
    // Asegurarse de que restaurants.value sea siempre un array
    if (response.data && Array.isArray(response.data)) {
      restaurants.value = response.data;
    } else if (response.data && Array.isArray(response.data.data)) {
      restaurants.value = response.data.data;
    } else {
      console.warn('La respuesta no tiene el formato esperado:', response.data);
      restaurants.value = [];
    }
    
    console.log('Restaurantes cargados:', restaurants.value);
  } catch (error) {
    console.error('Error al cargar restaurantes:', error);
    restaurants.value = []; // Asegurarnos que sea un array vacío en caso de error
  } finally {
    loading.value = false;
  }
});

// Función para cambiar categoría activa
function setActiveCategory(categoryId) {
  activeCategory.value = categoryId;
}

// Filtrar restaurantes por categoría
const filteredRestaurants = computed(() => {
  if (!restaurants.value || !Array.isArray(restaurants.value)) {
    return [];
  }
  
  if (activeCategory.value === 'all') {
    return restaurants.value;
  }
  
  return restaurants.value.filter(restaurant => 
    restaurant.categories && restaurant.categories.includes(activeCategory.value)
  );
});

// Restaurantes mejor valorados
const topRatedRestaurants = computed(() => {
  if (!restaurants.value || !Array.isArray(restaurants.value) || restaurants.value.length === 0) {
    return [];
  }
  
  return [...restaurants.value]
    .sort((a, b) => (b.rating || 0) - (a.rating || 0))
    .slice(0, 5);
});
</script>

<style scoped>
.main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.search-container {
  margin: 2rem auto;
  max-width: 700px;
  width: 100%;
}

.search-wrapper {
  display: flex;
  border: 1px solid var(--border-color);
  border-radius: 50px;
  overflow: hidden;
  background-color: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.search-input {
  flex: 1;
  padding: 0.875rem 1.5rem;
  border: none;
  outline: none;
  font-size: 1rem;
}

.search-button {
  background: var(--button-primary);
  border: none;
  color: white;
  padding: 0.875rem 1.5rem;
  cursor: pointer;
  font-size: 1.1rem;
}

.section-title {
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  font-weight: 600;
  color: var(--text-color);
}

.featured-section {
  margin-bottom: 3rem;
}

.restaurant-cards {
  display: flex;
  gap: 1.5rem;
  overflow-x: auto;
  padding: 0.5rem 0.25rem;
  margin: 0 -0.25rem;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
}

.restaurant-cards::-webkit-scrollbar {
  height: 6px;
}

.restaurant-cards::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.1);
  border-radius: 10px;
}

.categories-section {
  margin-bottom: 3rem;
}

.category-tabs {
  display: flex;
  gap: 1rem;
  overflow-x: auto;
  padding: 0.5rem 0.25rem;
  margin: 0 -0.25rem 1.5rem;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

.category-tabs::-webkit-scrollbar {
  display: none;
}

.category-tab {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border-radius: 50px;
  background-color: var(--card-bg);
  border: 1px solid var(--border-color);
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  white-space: nowrap;
  transition: all 0.2s;
  color: var(--text-color);
}

.category-tab.active {
  background: var(--button-primary);
  color: white;
  border-color: transparent;
}

.category-icon {
  font-size: 1.2rem;
}

.restaurant-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

/* Media queries para responsividad */
@media (max-width: 768px) {
  .section-title {
    font-size: 1.3rem;
    margin-bottom: 1.2rem;
  }
  
  .restaurant-cards {
    gap: 1rem;
  }
  
  .restaurant-grid {
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
  }
  
  .search-input {
    padding: 0.75rem 1.25rem;
  }
  
  .search-button {
    padding: 0.75rem 1.25rem;
  }
}

@media (max-width: 480px) {
  .main-container {
    padding: 0 0.75rem;
  }
  
  .search-container {
    margin: 1.5rem auto;
  }
  
  .category-tab {
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
  }
  
  .restaurant-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .restaurant-cards {
    scroll-snap-type: x mandatory;
    padding-bottom: 1rem;
  }
  
  .restaurant-cards > * {
    scroll-snap-align: start;
    flex: 0 0 80%;
  }
}
</style>