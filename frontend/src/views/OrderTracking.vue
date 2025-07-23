<!-- filepath: c:\xampp\htdocs\Sistema-delivery\frontend\src\views\OrderTracking.vue -->
<template>
  <div class="order-tracking">
    <div class="container" v-if="order">
      <div class="header">
        <h1>Seguimiento de Pedido #{{ order.id }}</h1>
        <div :class="['status-badge', statusClass]">{{ statusText }}</div>
      </div>
      
      <div class="restaurant-info">
        <img :src="order.restaurant?.logo || '/img/restaurant-placeholder.png'" alt="Logo del restaurante">
        <div>
          <h2>{{ order.restaurant?.name || 'Restaurante' }}</h2>
          <p>{{ order.restaurant?.address || 'Dirección no disponible' }}</p>
        </div>
      </div>
      
      <div class="tracking-timeline">
        <div class="timeline-progress-bar">
          <div class="progress" :style="{ width: progressPercentage + '%' }"></div>
        </div>
        
        <div class="timeline-steps">
          <div 
            v-for="(step, index) in trackingSteps" 
            :key="index"
            :class="['step', { 'active': isStepActive(step.status), 'completed': isStepCompleted(step.status) }]"
          >
            <div class="step-icon">{{ step.icon }}</div>
            <div class="step-content">
              <h3>{{ step.title }}</h3>
              <p v-if="getStepTime(step.status)" class="step-time">{{ getStepTime(step.status) }}</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="order-details">
        <h2>Detalles del Pedido</h2>
        <div class="items-list">
          <div v-for="item in order.items" :key="item.id" class="order-item">
            <div class="item-quantity">{{ item.quantity }}x</div>
            <div class="item-name">{{ item.product?.name || 'Producto' }}</div>
            <div class="item-price">${{ (item.price * item.quantity).toFixed(2) }}</div>
          </div>
        </div>
        
        <div class="order-totals">
          <div class="total-row">
            <span>Subtotal:</span>
            <span>${{ order.subtotal?.toFixed(2) || order.total?.toFixed(2) }}</span>
          </div>
          <div class="total-row" v-if="order.delivery_fee">
            <span>Envío:</span>
            <span>${{ order.delivery_fee?.toFixed(2) }}</span>
          </div>
          <div class="total-row total">
            <span>Total:</span>
            <span>${{ order.total?.toFixed(2) }}</span>
          </div>
        </div>
      </div>
      
      <div class="delivery-info" v-if="order.status !== 'pending' && order.status !== 'cancelled'">
        <h2>Información de Entrega</h2>
        <div class="delivery-address">
          <div class="address-icon">📍</div>
          <div class="address-content">
            <h3>Dirección de entrega</h3>
            <p>{{ order.address || 'Dirección no disponible' }}</p>
          </div>
        </div>
        
        <div class="estimated-time" v-if="estimatedTime">
          <div class="time-icon">⏱️</div>
          <div class="time-content">
            <h3>Tiempo estimado de entrega</h3>
            <p>{{ estimatedTime }}</p>
          </div>
        </div>
      </div>
      
      <div class="action-buttons">
        <button @click="$router.push('/orders')" class="btn btn-secondary">
          Ver mis pedidos
        </button>
        <button v-if="canCancel" @click="cancelOrder" class="btn btn-danger">
          Cancelar pedido
        </button>
      </div>
    </div>
    
    <div v-else class="container loading-state">
      <div v-if="loading" class="loading-spinner">
        <div class="spinner"></div>
        <p>Cargando información del pedido...</p>
      </div>
      <div v-else class="error-state">
        <h2>No se encontró el pedido</h2>
        <p>El pedido que buscas no existe o no tienes permisos para verlo.</p>
        <button @click="$router.push('/orders')" class="btn btn-primary">
          Ver mis pedidos
        </button>
      </div>
    </div>
    
    <!-- Agregar esto donde sea apropiado en el diseño -->
    <div v-if="!['delivered', 'cancelled'].includes(order?.status)" class="auto-update-indicator">
      <span>Actualizando en {{ nextUpdateIn }}s</span>
      <button @click="loadOrder().then(() => setupAutoUpdate())" class="refresh-button">
        Actualizar ahora
      </button>
    </div>
    
    <div v-if="order?.status === 'out_for_delivery'" class="delivery-map-container">
      <h3>Seguimiento de entrega</h3>
      <div class="map" ref="mapContainer"></div>
    </div>
    
    <DeliveryDriver 
      v-if="order?.status === 'out_for_delivery' && order?.delivery_info"
      :driver="order.delivery_info?.driver"
      :orderId="orderId"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/services/api';
import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';
import DeliveryDriver from '@/components/DeliveryDriver.vue';

// Variables reactivas
const order = ref(null);
const loading = ref(true);
const error = ref(null);
const updateTimer = ref(null);
const nextUpdateIn = ref(30);
const mapContainer = ref(null);
const map = ref(null);

const route = useRoute();
const orderId = route.params.id;

const trackingSteps = [
  { 
    status: 'pending', 
    title: 'Pedido Recibido', 
    icon: '📋' 
  },
  { 
    status: 'confirmed', 
    title: 'Pedido Confirmado', 
    icon: '✅' 
  },
  { 
    status: 'preparing', 
    title: 'Preparando', 
    icon: '👨‍🍳' 
  },
  { 
    status: 'ready', 
    title: 'Listo para entrega', 
    icon: '📦' 
  },
  { 
    status: 'out_for_delivery', 
    title: 'En camino', 
    icon: '🛵' 
  },
  { 
    status: 'delivered', 
    title: 'Entregado', 
    icon: '🎉' 
  }
];

// Status order: pending, confirmed, preparing, ready, out_for_delivery, delivered, cancelled
const statusMap = {
  'pending': { text: 'Pendiente', class: 'pending' },
  'confirmed': { text: 'Confirmado', class: 'confirmed' },
  'preparing': { text: 'Preparando', class: 'preparing' },
  'ready': { text: 'Listo', class: 'ready' },
  'out_for_delivery': { text: 'En camino', class: 'delivery' },
  'delivered': { text: 'Entregado', class: 'delivered' },
  'cancelled': { text: 'Cancelado', class: 'cancelled' }
};

const statusText = computed(() => {
  return order.value ? statusMap[order.value.status]?.text || 'Desconocido' : '';
});

const statusClass = computed(() => {
  return order.value ? statusMap[order.value.status]?.class || '' : '';
});

const progressPercentage = computed(() => {
  if (!order.value) return 0;
  
  const statusIndex = trackingSteps.findIndex(step => step.status === order.value.status);
  if (statusIndex === -1) return 0;
  
  return Math.min(100, (statusIndex / (trackingSteps.length - 1)) * 100);
});

const estimatedTime = computed(() => {
  if (!order.value || !order.value.estimated_delivery_time) return null;
  
  const deliveryTime = new Date(order.value.estimated_delivery_time);
  return formatDistanceToNow(deliveryTime, { addSuffix: true, locale: es });
});

const canCancel = computed(() => {
  if (!order.value) return false;
  return ['pending', 'confirmed'].includes(order.value.status);
});

function isStepActive(status) {
  return order.value?.status === status;
}

function isStepCompleted(status) {
  if (!order.value) return false;
  const currentIndex = trackingSteps.findIndex(step => step.status === order.value.status);
  const stepIndex = trackingSteps.findIndex(step => step.status === status);
  
  return stepIndex < currentIndex;
}

function getStepTime(status) {
  if (!order.value) return null;
  
  const timeField = `${status}_at`;
  if (!order.value[timeField]) return null;
  
  const date = new Date(order.value[timeField]);
  return formatDistanceToNow(date, { addSuffix: true, locale: es });
}

async function loadOrder() {
  try {
    loading.value = true;
    const response = await api.get(`/orders/${orderId}`);
    order.value = response.data;
    
    // Si el usuario no es el dueño del pedido, redirigir
    if (order.value.user_id !== localStorage.getItem('userId')) {
      router.push('/orders');
    }
  } catch (err) {
    error.value = err.message || 'Error al cargar el pedido';
    console.error('Error cargando el pedido:', err);
  } finally {
    loading.value = false;
  }
}

async function cancelOrder() {
  if (!confirm('¿Estás seguro que deseas cancelar este pedido?')) return;
  
  try {
    const response = await api.patch(`/orders/${orderId}/cancel`);
    order.value = response.data;
  } catch (err) {
    alert('No se pudo cancelar el pedido: ' + (err.response?.data?.message || err.message));
    console.error('Error cancelando el pedido:', err);
  }
}

// Función para configurar la actualización automática
function setupAutoUpdate() {
  // Limpiar cualquier timer existente
  if (updateTimer.value) {
    clearInterval(updateTimer.value);
  }
  
  // No configurar timers si el pedido está en estado final
  if (order.value && ['delivered', 'cancelled'].includes(order.value.status)) {
    return;
  }
  
  // Contar regresivamente para la próxima actualización
  nextUpdateIn.value = 30;
  const countdownInterval = setInterval(() => {
    nextUpdateIn.value--;
    
    if (nextUpdateIn.value <= 0) {
      clearInterval(countdownInterval);
    }
  }, 1000);
  
  // Configurar actualización cada 30 segundos
  updateTimer.value = setTimeout(() => {
    loadOrder().then(() => {
      setupAutoUpdate();
    });
  }, 30000);
}

// Función para inicializar el mapa
function initMap() {
  if (!order.value || order.value.status !== 'out_for_delivery') return;
  
  // Solo inicializar una vez
  if (map.value) return;
  
  // Configura tu token de Mapbox - reemplaza con el tuyo
  mapboxgl.accessToken = 'tu_token_de_mapbox';
  
  // Coordenadas del restaurante y del cliente
  const restaurantCoords = [order.value.restaurant?.longitude || -99.133209, order.value.restaurant?.latitude || 19.4326];
  const deliveryCoords = [order.value.delivery_longitude || -99.143209, order.value.delivery_latitude || 19.4326];
  
  // Inicializar mapa
  map.value = new mapboxgl.Map({
    container: mapContainer.value,
    style: 'mapbox://styles/mapbox/streets-v11',
    center: restaurantCoords,
    zoom: 13
  });
  
  // Añadir marcadores
  new mapboxgl.Marker({ color: '#3FB1CE' })
    .setLngLat(restaurantCoords)
    .setPopup(new mapboxgl.Popup().setHTML("<h3>Restaurante</h3><p>" + order.value.restaurant?.name + "</p>"))
    .addTo(map.value);
  
  new mapboxgl.Marker({ color: '#F84C4C' })
    .setLngLat(deliveryCoords)
    .setPopup(new mapboxgl.Popup().setHTML("<h3>Dirección de entrega</h3><p>" + order.value.address + "</p>"))
    .addTo(map.value);
}

// Observar cambios en el pedido para inicializar el mapa
watch(order, (newOrder) => {
  if (newOrder?.status === 'out_for_delivery') {
    // Esperar a que el DOM se actualice
    setTimeout(() => {
      initMap();
    }, 100);
  }
});

// Al montar el componente
onMounted(() => {
  loadOrder().then(() => {
    setupAutoUpdate();
    if (order.value?.status === 'out_for_delivery') {
      initMap();
    }
  });
});

// Al desmontar el componente, limpiar timers
onUnmounted(() => {
  if (updateTimer.value) {
    clearTimeout(updateTimer.value);
  }
});
</script>

<style scoped>
.order-tracking {
  padding: 2rem 1rem;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.container {
  max-width: 800px;
  margin: 0 auto;
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.header h1 {
  margin: 0;
  font-size: 1.75rem;
  color: #333;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 100px;
  font-weight: 600;
  font-size: 0.875rem;
}

.pending {
  background-color: #f9fafb;
  color: #6b7280;
  border: 1px solid #e5e7eb;
}

.confirmed {
  background-color: #eff6ff;
  color: #3b82f6;
  border: 1px solid #dbeafe;
}

.preparing {
  background-color: #fff7ed;
  color: #f59e0b;
  border: 1px solid #fed7aa;
}

.ready {
  background-color: #ecfdf5;
  color: #10b981;
  border: 1px solid #d1fae5;
}

.delivery {
  background-color: #eef2ff;
  color: #6366f1;
  border: 1px solid #e0e7ff;
}

.delivered {
  background-color: #f0fdf4;
  color: #22c55e;
  border: 1px solid #bbf7d0;
}

.cancelled {
  background-color: #fef2f2;
  color: #ef4444;
  border: 1px solid #fecaca;
}

.restaurant-info {
  display: flex;
  align-items: center;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.restaurant-info img {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  object-fit: cover;
  margin-right: 1rem;
}

.restaurant-info h2 {
  margin: 0 0 0.25rem 0;
  font-size: 1.25rem;
}

.restaurant-info p {
  margin: 0;
  color: #6b7280;
}

.tracking-timeline {
  margin-bottom: 2.5rem;
}

.timeline-progress-bar {
  height: 6px;
  background-color: #e5e7eb;
  border-radius: 100px;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;
}

.timeline-progress-bar .progress {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  background-color: #10b981;
  border-radius: 100px;
  transition: width 0.5s ease;
}

.timeline-steps {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.step-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
  font-size: 1.25rem;
  transition: all 0.3s ease;
}

.step.active .step-icon {
  background-color: #10b981;
  color: white;
  transform: scale(1.1);
  box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2);
}

.step.completed .step-icon {
  background-color: #d1fae5;
  color: #10b981;
}

.step h3 {
  margin: 0;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.step-time {
  margin: 0;
  font-size: 0.75rem;
  color: #6b7280;
}

.order-details {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background-color: #f9fafb;
  border-radius: 8px;
}

.order-details h2 {
  margin-top: 0;
  margin-bottom: 1rem;
  font-size: 1.25rem;
}

.items-list {
  margin-bottom: 1.5rem;
}

.order-item {
  display: flex;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.order-item:last-child {
  border-bottom: none;
}

.item-quantity {
  font-weight: 600;
  margin-right: 0.75rem;
  color: #6b7280;
}

.item-name {
  flex: 1;
}

.item-price {
  font-weight: 600;
}

.order-totals {
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
}

.total-row.total {
  font-weight: 600;
  font-size: 1.125rem;
  padding-top: 0.75rem;
  margin-top: 0.75rem;
  border-top: 1px dashed #e5e7eb;
}

.delivery-info {
  margin-bottom: 2rem;
}

.delivery-info h2 {
  margin-top: 0;
  margin-bottom: 1rem;
  font-size: 1.25rem;
}

.delivery-address, .estimated-time {
  display: flex;
  margin-bottom: 1rem;
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 8px;
}

.address-icon, .time-icon {
  font-size: 1.5rem;
  margin-right: 1rem;
}

.address-content h3, .time-content h3 {
  margin: 0;
  font-size: 1rem;
  margin-bottom: 0.25rem;
}

.address-content p, .time-content p {
  margin: 0;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 2rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-secondary {
  background-color: #f3f4f6;
  color: #1f2937;
}

.btn-secondary:hover {
  background-color: #e5e7eb;
}

.btn-danger {
  background-color: #ef4444;
  color: white;
}

.btn-danger:hover {
  background-color: #dc2626;
}

.loading-state {
  text-align: center;
  padding: 3rem 1rem;
}

.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state {
  text-align: center;
}

.auto-update-indicator {
  text-align: center;
  margin: 1.5rem 0;
  padding: 1rem;
  background-color: #eef2ff;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
}

.refresh-button {
  background-color: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s;
}

.refresh-button:hover {
  background-color: #2563eb;
}

.delivery-map-container {
  margin: 1.5rem 0;
  border-radius: 8px;
  overflow: hidden;
}

.map {
  height: 300px;
  width: 100%;
  border-radius: 8px;
}

/* Mejoras responsivas para OrderTracking.vue */
@media (max-width: 768px) {
  .container {
    padding: 1.5rem;
    border-radius: 8px;
  }
  
  .header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .restaurant-info {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .restaurant-info img {
    margin-right: 0;
    margin-bottom: 1rem;
    width: 80px;
    height: 80px;
  }

  .timeline-steps {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .step {
    flex-direction: row;
    text-align: left;
    align-items: flex-start;
  }
  
  .step-icon {
    margin-right: 1rem;
    margin-bottom: 0;
  }

  .delivery-address, .estimated-time {
    flex-direction: column;
  }
  
  .address-icon, .time-icon {
    margin-right: 0;
    margin-bottom: 0.75rem;
  }
  
  .order-item {
    flex-wrap: wrap;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .btn {
    width: 100%;
  }
  
  .map {
    height: 250px;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 1rem;
    border-radius: 6px;
  }
  
  .header h1 {
    font-size: 1.5rem;
  }
  
  .status-badge {
    padding: 0.35rem 0.75rem;
    font-size: 0.75rem;
  }
  
  .restaurant-info {
    padding: 0.75rem;
  }
  
  .restaurant-info img {
    width: 60px;
    height: 60px;
  }
  
  .order-details {
    padding: 1rem;
  }
  
  .timeline-progress-bar {
    margin-bottom: 1rem;
  }
  
  .step-icon {
    width: 36px;
    height: 36px;
    font-size: 1.1rem;
  }
  
  .auto-update-indicator {
    padding: 0.75rem;
    margin: 1rem 0;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .refresh-button {
    width: 100%;
  }
}

@media (max-width: 360px) {
  .container {
    padding: 0.75rem;
  }
  
  .timeline-steps {
    gap: 1.25rem;
  }
}
</style>