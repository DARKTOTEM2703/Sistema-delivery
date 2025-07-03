<!-- filepath: frontend/src/views/OwnerDashboard.vue -->
<template>
  <div class="owner-dashboard">
    <div class="dashboard-header">
      <h1>🏪 Mi Restaurante - {{ restaurant?.name }}</h1>
      <div class="header-actions">
        <button @click="showAddProduct = true" class="btn-primary">
          ➕ Agregar Producto
        </button>
      </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-grid">
      <div class="stat-card">
        <h3>📦 Pedidos Hoy</h3>
        <p class="stat-number">{{ stats.todayOrders }}</p>
      </div>
      <div class="stat-card">
        <h3>💰 Ingresos Hoy</h3>
        <p class="stat-number">${{ stats.todayRevenue }}</p>
      </div>
      <div class="stat-card">
        <h3>🍽️ Productos</h3>
        <p class="stat-number">{{ products.length }}</p>
      </div>
      <div class="stat-card">
        <h3>⭐ Calificación</h3>
        <p class="stat-number">{{ restaurant?.rating }}</p>
      </div>
    </div>

    <!-- Gestión de productos -->
    <div class="products-section">
      <h2>🍽️ Gestionar Menú</h2>
      
      <div class="products-grid">
        <div 
          v-for="product in products" 
          :key="product.id" 
          class="product-card"
        >
          <img :src="product.image" :alt="product.name" class="product-image">
          <div class="product-info">
            <h3>{{ product.name }}</h3>
            <p class="product-description">{{ product.description }}</p>
            <p class="product-price">${{ product.price }}</p>
            <div class="product-actions">
              <button @click="editProduct(product)" class="btn-edit">
                ✏️ Editar
              </button>
              <button @click="toggleProductStatus(product)" class="btn-toggle">
                {{ product.is_available ? '⏸️ Pausar' : '▶️ Activar' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para agregar/editar producto -->
    <ProductModal 
      v-if="showAddProduct || editingProduct"
      :product="editingProduct"
      :restaurant-id="restaurantId"
      @close="closeProductModal"
      @saved="handleProductSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import ProductModal from '@/components/ProductModal.vue'
import api from '@/services/api'

const route = useRoute()
const restaurantId = route.params.id

const restaurant = ref(null)
const products = ref([])
const stats = ref({
  todayOrders: 0,
  todayRevenue: 0
})
const showAddProduct = ref(false)
const editingProduct = ref(null)

onMounted(async () => {
  await loadRestaurant()
  await loadProducts()
  await loadStats()
})

const loadRestaurant = async () => {
  try {
    const response = await api.get(`/restaurants/${restaurantId}`)
    restaurant.value = response.data
  } catch (error) {
    console.error('Error cargando restaurante:', error)
  }
}

const loadProducts = async () => {
  try {
    const response = await api.get(`/restaurants/${restaurantId}/products`)
    products.value = response.data
  } catch (error) {
    console.error('Error cargando productos:', error)
  }
}

const loadStats = async () => {
  try {
    const response = await api.get(`/restaurants/${restaurantId}/dashboard-stats`)
    stats.value = response.data
  } catch (error) {
    console.error('Error cargando estadísticas:', error)
  }
}

const editProduct = (product) => {
  editingProduct.value = product
}

const closeProductModal = () => {
  showAddProduct.value = false
  editingProduct.value = null
}

const handleProductSaved = () => {
  closeProductModal()
  loadProducts() // Recargar productos
}

const toggleProductStatus = async (product) => {
  try {
    await api.put(`/products/${product.id}`, {
      ...product,
      is_available: !product.is_available
    })
    
    product.is_available = !product.is_available
  } catch (error) {
    console.error('Error actualizando producto:', error)
  }
}
</script>

<style scoped>
.owner-dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: var(--card-bg);
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-number {
  font-size: 2rem;
  font-weight: bold;
  color: var(--primary-color);
  margin: 0.5rem 0 0 0;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.product-card {
  background: var(--card-bg);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.product-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.product-info {
  padding: 1rem;
}

.product-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.btn-primary,
.btn-edit,
.btn-toggle {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-edit {
  background: #3b82f6;
  color: white;
}

.btn-toggle {
  background: #10b981;
  color: white;
}
</style>