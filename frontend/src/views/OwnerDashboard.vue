<!-- filepath: frontend/src/views/OwnerDashboard.vue -->
<template>
  <div class="owner-dashboard">
    <!-- Header del dashboard -->
    <div class="dashboard-header">
      <h1>{{ restaurant?.name || 'Panel de Control' }}</h1>
      
      <div class="header-actions">
        <!-- Control de estado del restaurante -->
        <div class="restaurant-status-control">
          <div :class="['status-indicator', restaurant?.is_open ? 'open' : 'closed']">
            {{ restaurant?.is_open ? 'Abierto' : 'Cerrado' }}
          </div>
          <button @click="toggleRestaurantStatus" class="btn-toggle-status">
            {{ restaurant?.is_open ? 'Cerrar Local' : 'Abrir Local' }}
          </button>
        </div>
        
        <router-link :to="`/pos/${restaurantId}`" class="btn-pos">
          <span>🧾 POS</span>
        </router-link>
      </div>
    </div>

    <!-- Tabs para navegar entre secciones -->
    <div class="dashboard-tabs">
      <button 
        @click="activeTab = 'orders'" 
        :class="['tab-btn', { active: activeTab === 'orders' }]"
      >
        🛍️ Pedidos
      </button>
      
      <button 
        @click="activeTab = 'menu'" 
        :class="['tab-btn', { active: activeTab === 'menu' }]"
      >
        🍽️ Menú
      </button>
      
      <button 
        @click="activeTab = 'inventory'" 
        :class="['tab-btn', { active: activeTab === 'inventory' }]"
      >
        📦 Inventario
      </button>
      
      <button 
        @click="activeTab = 'reports'" 
        :class="['tab-btn', { active: activeTab === 'reports' }]"
      >
        📊 Reportes
      </button>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-grid">
      <div class="stat-card">
        <h3>📦 Pedidos Pendientes</h3>
        <p class="stat-number urgent">{{ pendingOrdersCount }}</p>
      </div>
      <div class="stat-card">
        <h3>💰 Ingresos Hoy</h3>
        <p class="stat-number">${{ formatPrice(stats.todayRevenue || 0) }}</p>
      </div>
      <div class="stat-card">
        <h3>🍽️ Productos Activos</h3>
        <p class="stat-number">{{ products.filter(p => p.is_available).length }}</p>
      </div>
      <div class="stat-card">
        <h3>⭐ Calificación</h3>
        <p class="stat-number">{{ restaurant?.rating || '4.5' }}</p>
      </div>
    </div>

    <!-- Contenido según la pestaña activa -->
    <div class="tab-content">
      
      <!-- GESTIÓN DE PEDIDOS ONLINE -->
      <div v-if="activeTab === 'orders'" class="orders-management">
        <div class="orders-header">
          <h2>📋 Gestión de Pedidos en Tiempo Real</h2>
          <div class="auto-refresh">
            <span>🔄 Actualización automática cada 30s</span>
            <div :class="refreshIndicatorClass"></div>
          </div>
        </div>
        
        <!-- Filtros de estado -->
        <div class="order-filters">
          <button 
            v-for="status in orderStatuses" 
            :key="status.key"
            @click="selectedOrderStatus = status.key"
            :class="getFilterButtonClass(status.key)"
          >
            {{ status.icon }} {{ status.label }} 
            <span class="count">({{ getOrderCountByStatus(status.key) }})</span>
          </button>
        </div>

        <!-- Lista de pedidos -->
        <div v-if="filteredOrders.length > 0" class="orders-grid">
          <div 
            v-for="order in filteredOrders" 
            :key="order.id"
            :class="getOrderCardClass(order.status)"
          >
            <div class="order-header">
              <div class="order-number">#{{ order.order_number || order.id }}</div>
              <div class="order-time">
                {{ formatOrderTime(order.created_at) }}
                <span class="time-ago">({{ getTimeAgo(order.created_at) }})</span>
              </div>
              <div :class="getStatusBadgeClass(order.status)">
                {{ getStatusLabel(order.status) }}
              </div>
            </div>
            
            <div class="order-customer">
              <div class="customer-name">👤 {{ order.customer_name || 'Cliente Online' }}</div>
              <div class="customer-phone">📞 {{ order.customer_phone || 'No especificado' }}</div>
              <div class="customer-address">📍 {{ order.delivery_address || order.address || 'No especificada' }}</div>
            </div>
            
            <div class="order-items">
              <div class="items-header">🍽️ Productos:</div>
              <div v-for="item in order.items || []" :key="item.id" class="order-item">
                <span class="item-details">{{ item.quantity }}x {{ item.product?.name || item.name }}</span>
                <span class="item-price">${{ formatPrice(item.subtotal || (item.price * item.quantity)) }}</span>
              </div>
            </div>
            
            <div class="order-payment">
              <div class="payment-method">💳 {{ order.payment_method || 'No especificado' }}</div>
              <div class="order-total">
                <strong>Total: ${{ formatPrice(order.total) }}</strong>
              </div>
            </div>
            
            <!-- Acciones según el estado -->
            <div class="order-actions">
              <!-- Pedido pendiente -->
              <template v-if="order.status === 'pending'">
                <button 
                  @click="updateOrderStatus(order.id, 'confirmed')"
                  class="btn-action btn-accept"
                >
                  ✅ Aceptar Pedido
                </button>
                <button 
                  @click="cancelOrder(order.id)"
                  class="btn-action btn-cancel"
                >
                  ❌ Rechazar
                </button>
              </template>
              
              <!-- Pedido confirmado -->
              <template v-if="order.status === 'confirmed'">
                <button 
                  @click="updateOrderStatus(order.id, 'preparing')"
                  class="btn-action btn-preparing"
                >
                  🍳 Iniciar Preparación
                </button>
                <button 
                  @click="cancelOrder(order.id)"
                  class="btn-action btn-cancel"
                >
                  ❌ Cancelar
                </button>
              </template>
              
              <!-- En preparación -->
              <template v-if="order.status === 'preparing'">
                <button 
                  @click="updateOrderStatus(order.id, 'ready')"
                  class="btn-action btn-ready"
                >
                  📦 Listo para Entrega
                </button>
                <div class="prep-timer">
                  ⏰ Tiempo estimado: {{ order.prep_time || '20' }} min
                </div>
              </template>
              
              <!-- Listo para entrega -->
              <template v-if="order.status === 'ready'">
                <button 
                  @click="updateOrderStatus(order.id, 'out_for_delivery')"
                  class="btn-action btn-delivery"
                >
                  🚚 Marcar como Enviado
                </button>
              </template>
              
              <!-- En camino -->
              <template v-if="order.status === 'out_for_delivery'">
                <button 
                  @click="updateOrderStatus(order.id, 'delivered')"
                  class="btn-action btn-delivered"
                >
                  ✅ Marcar como Entregado
                </button>
              </template>
            </div>
          </div>
        </div>

        <!-- Estado vacío -->
        <div v-else class="empty-orders">
          <div class="empty-icon">📭</div>
          <h3>No hay pedidos {{ selectedOrderStatus === 'all' ? '' : getStatusLabel(selectedOrderStatus).toLowerCase() }}</h3>
          <p>Los nuevos pedidos aparecerán aquí automáticamente</p>
        </div>
      </div>

      <!-- GESTIÓN DE MENÚ -->
      <div v-if="activeTab === 'menu'" class="menu-management">
        <div class="products-section">
          <div class="section-header">
            <h2>🍽️ Gestionar Menú</h2>
            <button @click="showAddProduct = true" class="btn-primary">
              ➕ Agregar Producto
            </button>
          </div>
          
          <div v-if="products.length > 0" class="products-grid">
            <div 
              v-for="product in products" 
              :key="product.id" 
              class="product-card"
            >
              <img 
                :src="product.image || 'https://via.placeholder.com/300x200?text=Producto'" 
                :alt="product.name" 
                class="product-image"
              >
              <div class="product-info">
                <h3>{{ product.name }}</h3>
                <p class="product-description">{{ product.description }}</p>
                <p class="product-price">${{ formatPrice(product.price) }}</p>
                
                <!-- Estado del producto -->
                <div class="product-status">
                  <span :class="getProductStatusClass(product.is_available)">
                    {{ product.is_available ? '✅ Disponible' : '❌ No Disponible' }}
                  </span>
                </div>
                
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
          
          <div v-else class="empty-products">
            <div class="empty-icon">🍽️</div>
            <h3>No tienes productos en tu menú</h3>
            <p>Agrega tu primer producto para empezar a recibir pedidos</p>
            <button @click="showAddProduct = true" class="btn-primary">
              ➕ Agregar Primer Producto
            </button>
          </div>
        </div>
      </div>

      <!-- CONTROL DE INVENTARIO -->
      <div v-if="activeTab === 'inventory'" class="inventory-management">
        <div class="section-header">
          <h2>📦 Control de Inventario</h2>
          <button @click="showAddInventoryItem = true" class="btn-primary">
            ➕ Añadir Ingrediente
          </button>
        </div>
        
        <div class="inventory-filters">
          <div class="search-box">
            <input 
              type="text" 
              v-model="inventorySearch" 
              placeholder="Buscar ingrediente..." 
              class="inventory-search"
            />
          </div>
          
          <div class="filter-options">
            <select v-model="inventoryFilter" class="inventory-filter">
              <option value="all">Todos los ingredientes</option>
              <option value="low">Stock bajo</option>
              <option value="out">Agotados</option>
            </select>
          </div>
        </div>
        
        <div v-if="inventoryItems.length > 0" class="inventory-table-container">
          <table class="inventory-table">
            <thead>
              <tr>
                <th>Ingrediente</th>
                <th>Categoría</th>
                <th>Stock Actual</th>
                <th>Unidad</th>
                <th>Stock Mínimo</th>
                <th>Último Restock</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredInventory" :key="item.id" :class="getInventoryRowClass(item)">
                <td>{{ item.name }}</td>
                <td>{{ item.category }}</td>
                <td class="quantity-cell">
                  <span class="quantity">{{ item.current_stock }}</span>
                </td>
                <td>{{ item.unit }}</td>
                <td>{{ item.min_stock }}</td>
                <td>{{ formatDate(item.last_restock_at) }}</td>
                <td class="actions-cell">
                  <button @click="handleRestockClick(item)" class="btn-restock">
                    ↻ Restock
                  </button>
                  <button @click="handleEditInventoryItem(item)" class="btn-edit">
                    ✏️ Editar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Estado vacío -->
        <div v-else class="empty-inventory">
          <div class="empty-icon">📦</div>
          <h3>No hay ingredientes registrados</h3>
          <p>Añade ingredientes para gestionar tu inventario</p>
          <button @click="showAddInventoryItem = true" class="btn-primary">
            ➕ Añadir Primer Ingrediente
          </button>
        </div>
      </div>
      
      <!-- REPORTES -->
      <div v-if="activeTab === 'reports'" class="reports-section">
        <h2>📊 Reportes y Estadísticas</h2>
        
        <div class="reports-grid">
          <div class="report-card">
            <h3>📈 Ventas del Mes</h3>
            <p class="report-value">${{ formatPrice(stats.monthlyRevenue || 0) }}</p>
            <span class="report-detail">{{ stats.monthlyOrders || 0 }} pedidos</span>
          </div>
          
          <div class="report-card">
            <h3>🏆 Producto Más Vendido</h3>
            <p class="report-value">{{ topProduct?.name || 'N/A' }}</p>
            <span class="report-detail">{{ topProduct?.sales_count || 0 }} vendidos</span>
          </div>
          
          <div class="report-card">
            <h3>⭐ Calificación Promedio</h3>
            <p class="report-value">{{ restaurant?.rating || '4.5' }}/5</p>
            <span class="report-detail">{{ restaurant?.total_reviews || 0 }} reseñas</span>
          </div>
          
          <div class="report-card">
            <h3>⏱️ Tiempo Promedio</h3>
            <p class="report-value">{{ stats.avgDeliveryTime || '25' }} min</p>
            <span class="report-detail">Tiempo de entrega</span>
          </div>
        </div>
        
        <div class="report-actions">
          <button @click="showSalesReport = true" class="btn-detailed-report">
            📋 Ver Reporte Detallado
          </button>
          <button @click="exportSalesData" class="btn-export">
            📊 Exportar Datos
          </button>
        </div>
      </div>
    </div>

    <!-- Modales -->
    <ProductModal 
      v-if="showAddProduct || editingProduct"
      :product="editingProduct"
      :restaurant-id="restaurantId"
      @close="closeProductModal"
      @saved="handleProductSaved"
    />
    
    <SalesReportModal
      v-if="showSalesReport"
      :restaurant-id="restaurantId"
      @close="showSalesReport = false"
    />
    
    <InventoryItemModal 
      v-if="showAddInventoryItem"
      :restaurant-id="restaurantId"
      @close="showAddInventoryItem = false"
      @saved="handleInventoryItemSaved"
    />

    <InventoryItemModal 
      v-if="editingInventoryItem"
      :restaurant-id="restaurantId"
      :item="editingInventoryItem"
      @close="editingInventoryItem = null"
      @saved="handleInventoryItemSaved"
    />

    <RestockModal
      v-if="showRestockModal && selectedInventoryItem"
      :item="selectedInventoryItem"
      @close="closeRestockModal"
      @restocked="handleRestocked"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, inject } from 'vue'
import { useRoute } from 'vue-router'
import ProductModal from '@/components/ProductModal.vue'
import SalesReportModal from '@/components/SalesReportModal.vue'
// Importa los nuevos componentes
import InventoryItemModal from '@/components/InventoryItemModal.vue'
import RestockModal from '@/components/RestockModal.vue'
import api from '@/services/api'

const route = useRoute()
const restaurantId = route.params.id
const { success: showSuccess, error: showError } = inject('notifications')

// Estados existentes
const restaurant = ref(null)
const products = ref([])
const stats = ref({
  todayOrders: 0,
  todayRevenue: 0,
  monthlyOrders: 0,
  monthlyRevenue: 0
})
const showAddProduct = ref(false)
const editingProduct = ref(null)

// Nuevos estados para pedidos
const activeTab = ref('orders')
const orders = ref([])
const selectedOrderStatus = ref('all')
const isRefreshing = ref(false)
const showSalesReport = ref(false)
const topProduct = ref(null)

// Estados para inventario
const inventoryItems = ref([])
const inventorySearch = ref('')
const inventoryFilter = ref('all')
const showAddInventoryItem = ref(false)
const editingInventoryItem = ref(null)
const selectedInventoryItem = ref(null)
const showRestockModal = ref(false)

// Intervalos
let refreshInterval = null

// Estados de pedidos
const orderStatuses = [
  { key: 'all', label: 'Todos', icon: '📋' },
  { key: 'pending', label: 'Pendientes', icon: '🕐' },
  { key: 'confirmed', label: 'Confirmados', icon: '✅' },
  { key: 'preparing', label: 'En Preparación', icon: '🍳' },
  { key: 'ready', label: 'Listos', icon: '📦' },
  { key: 'out_for_delivery', label: 'En Entrega', icon: '🚚' },
  { key: 'delivered', label: 'Entregados', icon: '✅' },
  { key: 'cancelled', label: 'Cancelados', icon: '❌' }
]

// Computed properties
const filteredOrders = computed(() => {
  if (selectedOrderStatus.value === 'all') return orders.value
  return orders.value.filter(order => order.status === selectedOrderStatus.value)
})

const pendingOrdersCount = computed(() => {
  return orders.value.filter(order => ['pending', 'confirmed', 'preparing'].includes(order.status)).length
})

const refreshIndicatorClass = computed(() => {
  return ['refresh-indicator', { active: isRefreshing.value }]
})

// Computed para filtrar el inventario
const filteredInventory = computed(() => {
  let filtered = inventoryItems.value;
  
  // Filtrar por búsqueda
  if (inventorySearch.value) {
    const searchTerm = inventorySearch.value.toLowerCase();
    filtered = filtered.filter(item => 
      item.name.toLowerCase().includes(searchTerm) || 
      item.category.toLowerCase().includes(searchTerm)
    );
  }
  
  // Filtrar por estado de stock
  if (inventoryFilter.value === 'low') {
    filtered = filtered.filter(item => 
      item.current_stock <= item.min_stock && item.current_stock > 0
    );
  } else if (inventoryFilter.value === 'out') {
    filtered = filtered.filter(item => item.current_stock <= 0);
  }
  
  return filtered;
})

// Métodos para clases dinámicas
function getFilterButtonClass(statusKey) {
  return [
    'filter-btn',
    statusKey,
    { active: selectedOrderStatus.value === statusKey }
  ]
}

function getOrderCardClass(status) {
  return ['order-card', status]
}

function getStatusBadgeClass(status) {
  return ['order-status-badge', status]
}

function getProductStatusClass(isAvailable) {
  return ['status-badge', { available: isAvailable }]
}

// Función para obtener la clase de la fila según el nivel de stock
function getInventoryRowClass(item) {
  if (item.current_stock <= 0) {
    return 'out-of-stock';
  } else if (item.current_stock <= item.min_stock) {
    return 'low-stock';
  }
  return '';
}

// Formatear fecha
function formatDate(dateString) {
  if (!dateString) return 'Nunca';
  return new Date(dateString).toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Lifecycle
onMounted(async () => {
  await loadRestaurant()
  await loadProducts()
  await loadStats()
  await loadOrders()
  await loadInventory() // Agregar carga del inventario
  startAutoRefresh()
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})

// Métodos de carga
const loadRestaurant = async () => {
  try {
    const response = await api.get(`/restaurants/${restaurantId}`)
    restaurant.value = response.data
  } catch (error) {
    console.error('Error cargando restaurante:', error)
    showError?.('Error cargando datos del restaurante')
  }
}

const loadProducts = async () => {
  try {
    const response = await api.get(`/restaurants/${restaurantId}/products`)
    products.value = response.data
  } catch (error) {
    console.error('Error cargando productos:', error)
    // Fallback con productos de ejemplo
    products.value = [
      {
        id: 1,
        name: 'Pizza Margherita',
        description: 'Pizza clásica con tomate, mozzarella y albahaca fresca',
        price: 18.99,
        image: 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
        is_available: true,
        category: 'Pizzas'
      },
      {
        id: 2,
        name: 'Hamburguesa Clásica',
        description: 'Hamburguesa con carne, lechuga, tomate y queso',
        price: 12.99,
        image: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
        is_available: false,
        category: 'Hamburguesas'
      }
    ]
  }
}

const loadStats = async () => {
  try {
    const response = await api.get(`/restaurants/${restaurantId}/dashboard-stats`)
    stats.value = response.data
  } catch (error) {
    console.error('Error cargando estadísticas:', error)
    // Fallback con datos de ejemplo
    stats.value = {
      todayOrders: 8,
      todayRevenue: 245.50,
      monthlyOrders: 156,
      monthlyRevenue: 4230.75
    }
  }
}

const loadOrders = async () => {
  isRefreshing.value = true
  try {
    const response = await api.get(`/restaurants/${restaurantId}/orders`)
    orders.value = response.data || []
  } catch (error) {
    console.error('Error cargando pedidos:', error)
    // Datos de ejemplo para desarrollo
    orders.value = [
      {
        id: 1,
        order_number: 'ORD-001',
        status: 'pending',
        customer_name: 'Juan Pérez',
        customer_phone: '+52 555 123 4567',
        delivery_address: 'Calle Principal 123, Col. Centro',
        payment_method: 'Tarjeta de crédito',
        total: 125.50,
        created_at: new Date().toISOString(),
        items: [
          { id: 1, name: 'Pizza Margherita', quantity: 1, price: 89.99, subtotal: 89.99 },
          { id: 2, name: 'Refresco', quantity: 2, price: 17.75, subtotal: 35.50 }
        ]
      },
      {
        id: 2,
        order_number: 'ORD-002',
        status: 'preparing',
        customer_name: 'María García',
        customer_phone: '+52 555 987 6543',
        delivery_address: 'Av. Reforma 456, Col. Roma Norte',
        payment_method: 'Efectivo',
        total: 89.99,
        created_at: new Date(Date.now() - 15 * 60 * 1000).toISOString(),
        items: [
          { id: 3, name: 'Hamburguesa Clásica', quantity: 1, price: 89.99, subtotal: 89.99 }
        ]
      }
    ]
  } finally {
    isRefreshing.value = false
  }
}

const loadInventory = async () => {
  try {
    const response = await api.get(`/restaurants/${restaurantId}/inventory`)
    inventoryItems.value = response.data
  } catch (error) {
    console.error('Error cargando inventario:', error)
    showError?.('Error al cargar el inventario')
    inventoryItems.value = []
  }
}

const startAutoRefresh = () => {
  refreshInterval = setInterval(() => {
    loadOrders()
    loadStats()
  }, 30000) // Cada 30 segundos
}

const updateOrderStatus = async (orderId, newStatus) => {
  try {
    await api.patch(`/orders/${orderId}/status`, { status: newStatus })
    
    // Actualizar localmente
    const order = orders.value.find(o => o.id === orderId)
    if (order) {
      order.status = newStatus
    }
    
    showSuccess?.(`Pedido actualizado a: ${getStatusLabel(newStatus)}`)
    await loadStats()
  } catch (error) {
    console.error('Error actualizando estado del pedido:', error)
    showError?.('Error actualizando el estado del pedido')
  }
}

const cancelOrder = async (orderId) => {
  if (!confirm('¿Estás seguro de que quieres cancelar este pedido?')) return
  
  try {
    await api.patch(`/orders/${orderId}/cancel`)
    
    const order = orders.value.find(o => o.id === orderId)
    if (order) {
      order.status = 'cancelled'
    }
    
    showSuccess?.('Pedido cancelado exitosamente')
  } catch (error) {
    console.error('Error cancelando pedido:', error)
    showError?.('Error cancelando el pedido')
  }
}

const toggleRestaurantStatus = async () => {
  try {
    const newStatus = !restaurant.value?.is_open
    await api.patch(`/restaurants/${restaurantId}/toggle-status`, {
      is_open: newStatus
    })
    
    restaurant.value.is_open = newStatus
    
    showSuccess(
      newStatus 
        ? 'Restaurante abierto y listo para recibir pedidos' 
        : 'Restaurante cerrado temporalmente'
    )
  } catch (error) {
    console.error('Error cambiando estado del restaurante:', error)
    showError('No se pudo cambiar el estado del restaurante')
  }
}

// Métodos helper
const getStatusLabel = (status) => {
  const statusObj = orderStatuses.find(s => s.key === status)
  return statusObj?.label || status
}

const getOrderCountByStatus = (status) => {
  if (status === 'all') return orders.value.length
  return orders.value.filter(order => order.status === status).length
}

const formatOrderTime = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getTimeAgo = (timestamp) => {
  const now = new Date()
  const orderTime = new Date(timestamp)
  const diffMinutes = Math.floor((now - orderTime) / (1000 * 60))
  
  if (diffMinutes < 1) return 'Ahora'
  if (diffMinutes < 60) return `${diffMinutes} min`
  const diffHours = Math.floor(diffMinutes / 60)
  return `${diffHours}h ${diffMinutes % 60}min`
}

// Define la función formatPrice correctamente
function formatPrice(price) {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price;
  return isNaN(numPrice) ? '0.00' : numPrice.toFixed(2);
}

// Métodos de productos
const editProduct = (product) => {
  editingProduct.value = product
}

const closeProductModal = () => {
  showAddProduct.value = false
  editingProduct.value = null
}

const handleProductSaved = () => {
  closeProductModal()
  loadProducts()
  showSuccess?.('Producto guardado exitosamente')
}

const toggleProductStatus = async (product) => {
  try {
    await api.put(`/products/${product.id}`, {
      ...product,
      is_available: !product.is_available
    })
    
    product.is_available = !product.is_available
    showSuccess?.(`Producto ${product.is_available ? 'activado' : 'pausado'}`)
  } catch (error) {
    console.error('Error actualizando producto:', error)
    showError?.('Error actualizando el producto')
  }
}

const exportSalesData = () => {
  showSuccess?.('Función de exportación en desarrollo')
}

// Funciones para manejar el inventario
const handleEditInventoryItem = (item) => {
  editingInventoryItem.value = item
}

const handleInventoryItemSaved = async () => {
  showAddInventoryItem.value = false
  editingInventoryItem.value = null
  showSuccess?.('Ingrediente guardado exitosamente')
  await loadInventory()
}

const handleRestockClick = (item) => {
  selectedInventoryItem.value = item
  showRestockModal.value = true
}

const closeRestockModal = () => {
  showRestockModal.value = false
  selectedInventoryItem.value = null
}

const handleRestocked = async () => {
  showSuccess?.('Stock actualizado exitosamente')
  await loadInventory()
}
</script>

<style scoped>
/* ✅ VARIABLES CSS PARA TEMAS (AGREGAR AL INICIO) */
:root {
  /* Tema claro (por defecto) */
  --bg-primary: #ffffff;
  --bg-secondary: #f8fafc;
  --bg-tertiary: #f1f5f9;
  --text-primary: #1f2937;
  --text-secondary: #6b7280;
  --text-muted: #9ca3af;
  --border-color: #e5e7eb;
  --border-light: #e2e8f0;
  --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
  --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
  --shadow-lg: 0 8px 25px rgba(0,0,0,0.15);
}

/* Tema oscuro */
@media (prefers-color-scheme: dark) {
  :root {
    --bg-primary: #111827;
    --bg-secondary: #1f2937;
    --bg-tertiary: #374151;
    --text-primary: #f9fafb;
    --text-secondary: #d1d5db;
    --text-muted: #9ca3af;
    --border-color: #374151;
    --border-light: #4b5563;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.3);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.3);
    --shadow-lg: 0 8px 25px rgba(0,0,0,0.4);
  }
}

/* ✅ TU DISEÑO EXACTO, SOLO CAMBIANDO COLORES FIJOS POR VARIABLES */
.owner-dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
  background: var(--bg-primary);
  color: var(--text-primary);
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.dashboard-header h1 {
  color: var(--text-primary);
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.btn-pos {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-decoration: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-pos:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.dashboard-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  border-bottom: 2px solid var(--border-light);
}

.tab-btn {
  padding: 1rem 1.5rem;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
  color: var(--text-secondary);
}

.tab-btn.active {
  border-bottom-color: #3b82f6;
  background: #eff6ff;
  color: #1d4ed8;
}

.tab-btn:hover:not(.active) {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: var(--bg-primary);
  padding: 1.5rem;
  border-radius: 12px; /* Bordes más redondeados */
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.03);
  border: 1px solid var(--border-color);
  transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); /* Transición más suave */
  position: relative;
  overflow: hidden;
  cursor: pointer;
}

.stat-card:hover {
  transform: translateY(-5px); /* Mayor elevación */
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1), 0 3px 6px rgba(0, 0, 0, 0.05);
  border-color: rgba(59, 130, 246, 0.3); /* Borde azulado sutil */
}

/* Efecto de brillo en el fondo al pasar el cursor */
.stat-card::after {
  content: "";
  position: absolute;
  top: 0;
  left: -50%;
  width: 150%;
  height: 100%;
  background: linear-gradient(
    to right,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.1) 50%,
    rgba(255, 255, 255, 0) 100%
  );
  transform: translateX(-100%);
  transition: all 0s;
  opacity: 0;
}

.stat-card:hover::after {
  transform: translateX(100%);
  opacity: 1;
  transition: all 0.7s;
}

/* Ajuste para el texto de valor de la estadística */
.stat-number {
  font-size: 2.25rem;
  font-weight: 700;
  color: #3b82f6;
  margin: 0.75rem 0 0 0;
  transition: all 0.3s ease;
}

.stat-card:hover .stat-number {
  transform: scale(1.05);
  color: #2563eb; /* Azul más vibrante en hover */
}

/* Para el color rojo de pedidos pendientes */
.stat-number.urgent {
  color: #ef4444;
}

.stat-card:hover .stat-number.urgent {
  color: #dc2626; /* Rojo más vibrante en hover */
}

.order-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: 8px;
  border: 1px solid var(--border-color);
}

.filter-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--bg-primary);
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.filter-btn:hover {
  border-color: #3b82f6;
  background: #eff6ff;
}

.filter-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.filter-btn .count {
  font-weight: 600;
  margin-left: 0.25rem;
}

.orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1.5rem;
}

.order-card {
  background: var(--bg-primary);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  border-left: 4px solid;
  transition: transform 0.2s, box-shadow 0.2s;
}

.order-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.order-card.pending { border-left-color: #f59e0b; }
.order-card.confirmed { border-left-color: #3b82f6; }
.order-card.preparing { border-left-color: #8b5cf6; }
.order-card.ready { border-left-color: #10b981; }
.order-card.out_for_delivery { border-left-color: #06b6d4; }
.order-card.delivered { border-left-color: #22c55e; }
.order-card.cancelled { border-left-color: #ef4444; }

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border-color);
}

.order-number {
  font-weight: bold;
  color: var(--text-primary);
  font-size: 1.1rem;
}

.order-time {
  text-align: right;
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.time-ago {
  display: block;
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 0.25rem;
}

.order-status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
  color: white;
}

.order-status-badge.pending { background: #f59e0b; }
.order-status-badge.confirmed { background: #3b82f6; }
.order-status-badge.preparing { background: #8b5cf6; }
.order-status-badge.ready { background: #10b981; }
.order-status-badge.out_for_delivery { background: #06b6d4; }
.order-status-badge.delivered { background: #22c55e; }
.order-status-badge.cancelled { background: #ef4444; }

.order-customer {
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.customer-name {
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}

.customer-phone, .customer-address {
  color: var(--text-secondary);
  margin-bottom: 0.25rem;
}

.order-items {
  margin-bottom: 1rem;
}

.items-header {
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.25rem 0;
  font-size: 0.85rem;
}

.item-details {
  color: var(--text-secondary);
}

.item-price {
  font-weight: 500;
  color: #059669;
}

.order-payment {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: var(--bg-secondary);
  border-radius: 6px;
  border: 1px solid var(--border-color);
}

.payment-method {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.order-total {
  font-size: 1.1rem;
  color: #059669;
}

.order-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.btn-action {
  flex: 1;
  min-width: 120px;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-accept { background: #10b981; color: white; }
.btn-preparing { background: #8b5cf6; color: white; }
.btn-ready { background: #3b82f6; color: white; }
.btn-delivery { background: #06b6d4; color: white; }
.btn-delivered { background: #22c55e; color: white; }
.btn-cancel { background: #ef4444; color: white; }

.btn-action:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.prep-timer {
  width: 100%;
  padding: 0.5rem;
  background: #fef3c7;
  color: #92400e;
  border-radius: 4px;
  font-size: 0.8rem;
  text-align: center;
  margin-top: 0.5rem;
}

.empty-orders, .empty-products, .empty-inventory {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-secondary);
  background: var(--bg-secondary);
  border-radius: 8px;
  border: 1px solid var(--border-color);
}

.empty-orders h3, .empty-products h3, .empty-inventory h3 {
  color: var(--text-primary);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h2 {
  color: var(--text-primary);
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.product-card {
  background: var(--bg-primary);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
  transition: transform 0.2s;
}

.product-card:hover {
  transform: translateY(-2px);
}

.product-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.product-info {
  padding: 1rem;
}

.product-info h3 {
  color: var(--text-primary);
}

.product-description {
  color: var(--text-secondary);
  font-size: 0.9rem;
  margin: 0.5rem 0;
}

.product-price {
  font-size: 1.25rem;
  font-weight: bold;
  color: #059669;
  margin: 0.5rem 0;
}

.product-status {
  margin: 0.75rem 0;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
  background: #fee2e2;
  color: #dc2626;
}

.status-badge.available {
  background: #d1fae5;
  color: #065f46;
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
  transition: all 0.2s;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-edit {
  background: #f59e0b;
  color: white;
}

.btn-toggle {
  background: #10b981;
  color: white;
}

.btn-primary:hover,
.btn-edit:hover,
.btn-toggle:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

/* Reportes */
.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.report-card {
  background: var(--bg-primary);
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  border-top: 4px solid #3b82f6;
}

.report-card h3 {
  color: var(--text-secondary);
}

.report-value {
  font-size: 2.5rem;
  font-weight: bold;
  color: var(--text-primary);
  margin: 1rem 0;
}

.report-detail {
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.report-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-detailed-report,
.btn-export {
  padding: 1rem 2rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-detailed-report {
  background: #3b82f6;
  color: white;
}

.btn-export {
  background: #10b981;
  color: white;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .header-actions {
    justify-content: center;
  }
  
  .dashboard-tabs {
    flex-wrap: wrap;
  }
  
  .order-filters {
    justify-content: center;
  }
  
  .orders-grid {
    grid-template-columns: 1fr;
  }
  
  .order-actions {
    flex-direction: column;
  }
  
  .btn-action {
    min-width: auto;
  }
}

/* ✅ TRANSICIÓN SUAVE PARA CAMBIOS DE TEMA */
* {
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}
</style>