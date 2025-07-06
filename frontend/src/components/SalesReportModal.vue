<template>
  <div class="sales-report-modal">
    <div class="modal-overlay" @click="closeModal"></div>
    <div class="modal-content">
      <!-- Header del modal -->
      <div class="modal-header">
        <h2>📊 Reporte de Ventas</h2>
        <button @click="closeModal" class="close-btn">×</button>
      </div>

      <!-- Filtros de fecha -->
      <div class="modal-body">
        <div class="date-filters">
          <div class="filter-group">
            <label for="startDate">Fecha inicio:</label>
            <input 
              type="date" 
              id="startDate" 
              v-model="filters.startDate"
              @change="loadSalesData"
            />
          </div>
          <div class="filter-group">
            <label for="endDate">Fecha fin:</label>
            <input 
              type="date" 
              id="endDate" 
              v-model="filters.endDate"
              @change="loadSalesData"
            />
          </div>
          <button @click="applyFilters" class="btn-filter">Filtrar</button>
        </div>

        <!-- Resumen de ventas -->
        <div class="sales-summary" v-if="salesData">
          <div class="summary-card">
            <h3>💰 Total Ventas</h3>
            <p class="amount">${{ formatMoney(salesData.totalSales) }}</p>
          </div>
          <div class="summary-card">
            <h3>📦 Total Pedidos</h3>
            <p class="count">{{ salesData.totalOrders }}</p>
          </div>
          <div class="summary-card">
            <h3>📈 Promedio por Pedido</h3>
            <p class="amount">${{ formatMoney(salesData.averageOrder) }}</p>
          </div>
        </div>

        <!-- Tabla de detalles -->
        <div class="sales-details" v-if="salesData?.orders?.length">
          <h3>Detalle de Pedidos</h3>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Fecha</th>
                  <th>Cliente</th>
                  <th>Total</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in salesData.orders" :key="order.id">
                  <td>#{{ order.id }}</td>
                  <td>{{ formatDate(order.created_at) }}</td>
                  <td>{{ order.customer_name || 'Cliente' }}</td>
                  <td>${{ formatMoney(order.total) }}</td>
                  <td>
                    <span :class="getStatusClass(order.status)">
                      {{ order.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="loading">
          <p>📊 Cargando datos...</p>
        </div>

        <!-- Empty state -->
        <div v-if="!loading && (!salesData?.orders?.length)" class="empty-state">
          <p>📭 No hay datos para el período seleccionado</p>
        </div>
      </div>

      <!-- Footer con botones -->
      <div class="modal-footer">
        <button @click="exportPDF" class="btn-export">📄 Exportar PDF</button>
        <button @click="exportExcel" class="btn-export">📊 Exportar Excel</button>
        <button @click="closeModal" class="btn-cancel">Cerrar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/services/api'

// Props
const props = defineProps({
  restaurantId: {
    type: [String, Number],
    required: true
  }
})

// Emits
const emit = defineEmits(['close'])

// Estado reactivo
const loading = ref(false)
const salesData = ref(null)
const filters = ref({
  startDate: getDefaultStartDate(),
  endDate: getDefaultEndDate()
})

// Métodos
function closeModal() {
  emit('close')
}

function getDefaultStartDate() {
  const date = new Date()
  date.setDate(date.getDate() - 30) // Últimos 30 días
  return date.toISOString().split('T')[0]
}

function getDefaultEndDate() {
  const date = new Date()
  return date.toISOString().split('T')[0]
}

async function loadSalesData() {
  loading.value = true
  try {
    const response = await api.getSalesReport(props.restaurantId, {
      start_date: filters.value.startDate,
      end_date: filters.value.endDate
    })
    
    salesData.value = response.data
    
  } catch (error) {
    console.error('Error cargando datos de ventas:', error)
    salesData.value = null
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  loadSalesData()
}

function formatMoney(amount) {
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('es-MX', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getStatusClass(status) {
  const statusClasses = {
    'completado': 'status-completed',
    'entregado': 'status-delivered',
    'pendiente': 'status-pending',
    'cancelado': 'status-cancelled'
  }
  return statusClasses[status] || 'status-default'
}

function exportPDF() {
  console.log('Exportando reporte PDF...')
}

function exportExcel() {
  console.log('Exportando reporte Excel...')
}

// Cargar datos cuando se monta el componente
onMounted(() => {
  loadSalesData()
})

watch(() => props.restaurantId, () => {
  if (props.restaurantId) {
    loadSalesData()
  }
})
</script>

<style scoped>
.sales-report-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
}

.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
}

.modal-content {
  position: relative;
  background: white;
  border-radius: 12px;
  max-width: 900px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 12px 12px 0 0;
}

.modal-header h2 {
  margin: 0;
  color: #2d3748;
  font-size: 1.5rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #a0aec0;
  padding: 0.5rem;
  border-radius: 50%;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #e2e8f0;
  color: #4a5568;
}

.modal-body {
  padding: 1.5rem;
}

.date-filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group label {
  font-weight: 500;
  color: #4a5568;
  font-size: 0.9rem;
}

.filter-group input {
  padding: 0.5rem;
  border: 1px solid #d2d6dc;
  border-radius: 6px;
  font-size: 0.9rem;
}

.btn-filter {
  padding: 0.5rem 1rem;
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-filter:hover {
  background: #4338ca;
}

.sales-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.summary-card {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
  border: 1px solid #e2e8f0;
}

.summary-card h3 {
  margin: 0 0 0.5rem 0;
  color: #4a5568;
  font-size: 0.9rem;
}

.summary-card .amount {
  font-size: 1.8rem;
  font-weight: 700;
  color: #059669;
  margin: 0;
}

.summary-card .count {
  font-size: 1.8rem;
  font-weight: 700;
  color: #3b82f6;
  margin: 0;
}

.sales-details h3 {
  margin-bottom: 1rem;
  color: #2d3748;
}

.table-container {
  overflow-x: auto;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

th {
  background: #f8fafc;
  font-weight: 600;
  color: #4a5568;
  font-size: 0.9rem;
}

.status-completed { color: #059669; font-weight: 500; }
.status-delivered { color: #3b82f6; font-weight: 500; }
.status-pending { color: #d97706; font-weight: 500; }
.status-cancelled { color: #dc2626; font-weight: 500; }

.loading, .empty-state {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 0 0 12px 12px;
}

.btn-export, .btn-cancel {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-export {
  background: #059669;
  color: white;
}

.btn-export:hover {
  background: #047857;
}

.btn-cancel {
  background: #6b7280;
  color: white;
  margin-left: auto;
}

.btn-cancel:hover {
  background: #4b5563;
}

@media (max-width: 768px) {
  .date-filters {
    flex-direction: column;
    align-items: stretch;
  }
  
  .sales-summary {
    grid-template-columns: 1fr;
  }
  
  .modal-footer {
    flex-direction: column;
  }
}
</style>