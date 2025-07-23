<!-- filepath: c:\xampp\htdocs\Sistema-delivery\frontend\src\views\POSView.vue -->
<template>
  <div class="pos-container">
    <!-- Header del POS -->
    <div class="pos-header">
      <div class="restaurant-info">
        <h1>🏪 {{ restaurant?.name }} - POS</h1>
        <div class="shift-info" v-if="currentShift">
          <span class="shift-badge">Turno Activo</span>
          <span>Caja: ${{ currentShift.opening_cash }}</span>
          <span>{{ formatTime(currentShift.opened_at) }}</span>
        </div>
        <button v-else @click="showOpenShift = true" class="open-shift-btn">
          🔓 Abrir Turno
        </button>
      </div>
      
      <div class="pos-actions">
        <button @click="showSalesReport = true" class="action-btn">
          📊 Reporte
        </button>
        <button v-if="currentShift" @click="showCloseShift = true" class="action-btn danger">
          🔒 Cerrar Turno
        </button>
      </div>
    </div>

    <!-- Panel principal del POS -->
    <div class="pos-main">
      <!-- Panel izquierdo - Productos -->
      <div class="products-panel">
        <div class="search-bar">
          <input 
            v-model="searchTerm" 
            placeholder="🔍 Buscar productos..."
            class="search-input"
          >
        </div>
        
        <div class="categories">
          <button 
            v-for="category in categories"
            :key="category"
            @click="selectedCategory = category"
            :class="['category-btn', { active: selectedCategory === category }]"
          >
            {{ getCategoryIcon(category) }} {{ category }}
          </button>
        </div>
        
        <div class="products-grid">
          <div 
            v-for="product in filteredProducts"
            :key="product.id"
            @click="addToOrder(product)"
            class="product-tile"
          >
            <img :src="product.image" :alt="product.name" class="product-image">
            <div class="product-info">
              <h4>{{ product.name }}</h4>
              <p class="product-price">${{ formatPrice(product.price) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Panel derecho - Orden actual -->
      <div class="order-panel">
        <div class="order-header">
          <h3>🧾 Orden Actual</h3>
          <button @click="clearOrder" class="clear-btn">🗑️ Limpiar</button>
        </div>
        
        <div class="order-items">
          <div 
            v-for="item in currentOrder.items"
            :key="item.id"
            class="order-item"
          >
            <div class="item-info">
              <span class="item-name">{{ item.name }}</span>
              <span class="item-price">${{ formatPrice(item.price) }}</span>
            </div>
            <div class="item-controls">
              <button @click="decreaseQuantity(item)" class="qty-btn">-</button>
              <span class="quantity">{{ item.quantity }}</span>
              <button @click="increaseQuantity(item)" class="qty-btn">+</button>
              <button @click="removeItem(item)" class="remove-btn">🗑️</button>
            </div>
          </div>
        </div>
        
        <!-- Resumen de la orden -->
        <div class="order-summary">
          <div class="summary-line">
            <span>Subtotal:</span>
            <span>${{ formatPrice(currentOrder.subtotal) }}</span>
          </div>
          <div class="summary-line">
            <span>IVA (16%):</span>
            <span>${{ formatPrice(currentOrder.tax) }}</span>
          </div>
          <div class="summary-line total">
            <span><strong>Total:</strong></span>
            <span><strong>${{ formatPrice(currentOrder.total) }}</strong></span>
          </div>
        </div>
        
        <!-- Información del cliente -->
        <div class="customer-section">
          <h4>👤 Cliente</h4>
          <input 
            v-model="currentOrder.customer_name"
            placeholder="Nombre del cliente (opcional)"
            class="customer-input"
          >
          <input 
            v-model="currentOrder.customer_phone"
            placeholder="Teléfono (opcional)"
            class="customer-input"
          >
        </div>
        
        <!-- Botones de pago -->
        <div class="payment-section">
          <h4>💳 Método de Pago</h4>
          <div class="payment-buttons">
            <button 
              @click="processPayment('cash')" 
              class="payment-btn cash"
              :disabled="!canProcessOrder"
            >
              💵 Efectivo
            </button>
            <button 
              @click="processPayment('card')" 
              class="payment-btn card"
              :disabled="!canProcessOrder"
            >
              💳 Tarjeta
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modales -->
    <CashPaymentModal 
      v-if="showCashModal"
      :total="currentOrder.total"
      @close="showCashModal = false"
      @confirm="completeCashPayment"
    />
    
    <OpenShiftModal
      v-if="showOpenShift"
      @close="showOpenShift = false"
      @confirm="openShift"
    />
    
    <CloseShiftModal
      v-if="showCloseShift"
      :shift="currentShift"
      @close="showCloseShift = false"
      @confirm="closeShift"
    />
    
    <SalesReportModal
      v-if="showSalesReport"
      :restaurant-id="restaurantId"
      @close="showSalesReport = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import CashPaymentModal from '@/components/CashPaymentModal.vue'
import OpenShiftModal from '@/components/OpenShiftModal.vue'
import CloseShiftModal from '@/components/CloseShiftModal.vue'
import SalesReportModal from '@/components/SalesReportModal.vue'

const route = useRoute()
const auth = inject('auth')
const { success: showSuccess, error: showError } = inject('notifications')

// Estados principales
const restaurantId = route.params.id
const restaurant = ref(null)
const currentShift = ref(null)
const products = ref([])
const categories = ref(['todas'])
const selectedCategory = ref('todas')
const searchTerm = ref('')

// Estado de la orden
const currentOrder = ref({
  items: [],
  subtotal: 0,
  tax: 0,
  total: 0,
  customer_name: '',
  customer_phone: ''
})

// Modales
const showCashModal = ref(false)
const showOpenShift = ref(false)
const showCloseShift = ref(false)
const showSalesReport = ref(false)

// Computed
const filteredProducts = computed(() => {
  let filtered = products.value

  if (selectedCategory.value !== 'todas') {
    filtered = filtered.filter(p => p.category === selectedCategory.value)
  }

  if (searchTerm.value) {
    filtered = filtered.filter(p => 
      p.name.toLowerCase().includes(searchTerm.value.toLowerCase())
    )
  }

  return filtered
})

const canProcessOrder = computed(() => {
  return currentOrder.value.items.length > 0 && currentShift.value
})

// Métodos
function addToOrder(product) {
  const existingItem = currentOrder.value.items.find(item => item.id === product.id)
  
  if (existingItem) {
    existingItem.quantity++
  } else {
    currentOrder.value.items.push({
      ...product,
      quantity: 1
    })
  }
  
  calculateTotal()
}

function increaseQuantity(item) {
  item.quantity++
  calculateTotal()
}

function decreaseQuantity(item) {
  if (item.quantity > 1) {
    item.quantity--
  } else {
    removeItem(item)
  }
  calculateTotal()
}

function removeItem(item) {
  const index = currentOrder.value.items.findIndex(i => i.id === item.id)
  if (index !== -1) {
    currentOrder.value.items.splice(index, 1)
  }
  calculateTotal()
}

function clearOrder() {
  currentOrder.value = {
    items: [],
    subtotal: 0,
    tax: 0,
    total: 0,
    customer_name: '',
    customer_phone: ''
  }
}

function calculateTotal() {
  const subtotal = currentOrder.value.items.reduce((sum, item) => {
    return sum + (item.price * item.quantity)
  }, 0)
  
  const tax = subtotal * 0.16 // 16% IVA
  
  currentOrder.value.subtotal = subtotal
  currentOrder.value.tax = tax
  currentOrder.value.total = subtotal + tax
}

async function processPayment(method) {
  if (method === 'cash') {
    showCashModal.value = true
  } else {
    await completeOrder(method, currentOrder.value.total, 0)
  }
}

async function completeCashPayment({ cashReceived, change }) {
  await completeOrder('pos_cash', cashReceived, change)
  showCashModal.value = false
}

async function completeOrder(paymentMethod, cashReceived = 0, change = 0) {
  try {
    const orderData = {
      restaurant_id: restaurantId,
      order_type: 'pos',
      items: currentOrder.value.items.map(item => ({
        id: item.id,
        quantity: item.quantity,
        price: item.price
      })),
      total: currentOrder.value.total,
      subtotal: currentOrder.value.subtotal,
      tax_amount: currentOrder.value.tax,
      customer_name: currentOrder.value.customer_name || 'Cliente POS',
      customer_phone: currentOrder.value.customer_phone || 'N/A',
      payment_method: paymentMethod,
      cash_received: cashReceived,
      change_given: change,
      cash_register_id: 1 // Usar caja principal
    }

    const response = await api.post('/orders', orderData)
    
    // Mostrar ticket
    printReceipt(response.data.order)
    
    // Limpiar orden
    clearOrder()
    
    showSuccess('✅ Venta completada exitosamente')
    
  } catch (error) {
    showError('❌ Error al procesar la venta')
    console.error('Error:', error)
  }
}

function printReceipt(order) {
  const printWindow = window.open('', '_blank')
  const receiptHTML = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Ticket ${order.order_number}</title>
      <style>
        body { font-family: 'Courier New', monospace; width: 300px; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 10px; }
        .item { display: flex; justify-content: space-between; margin: 5px 0; }
        .total { border-top: 1px solid #000; padding-top: 10px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; }
      </style>
    </head>
    <body>
      <div class="header">
        <h2>🏪 ${restaurant.value?.name}</h2>
        <p>Ticket: ${order.receipt_number}</p>
        <p>Fecha: ${new Date().toLocaleString('es-ES')}</p>
        <p>Cajero: ${auth.getUser().name}</p>
        ${order.customer_name !== 'Cliente POS' ? `<p>Cliente: ${order.customer_name}</p>` : ''}
      </div>
      
      <div class="items">
        ${order.items?.map(item => `
          <div class="item">
            <span>${item.quantity}x ${item.product?.name || item.name}</span>
            <span>$${(item.price * item.quantity).toFixed(2)}</span>
          </div>
        `).join('') || ''}
      </div>
      
      <div class="total">
        <div class="item">
          <span>Subtotal:</span>
          <span>$${order.subtotal}</span>
        </div>
        <div class="item">
          <span>IVA (16%):</span>
          <span>$${order.tax_amount}</span>
        </div>
        <div class="item">
          <strong>Total: $${order.total}</strong>
        </div>
        ${order.cash_received ? `
          <div class="item">
            <span>Efectivo:</span>
            <span>$${order.cash_received}</span>
          </div>
          <div class="item">
            <span>Cambio:</span>
            <span>$${order.change_given}</span>
          </div>
        ` : ''}
      </div>
      
      <div class="footer">
        <p>¡Gracias por su preferencia!</p>
        <p>🌟 Califícanos en nuestras redes sociales</p>
      </div>
    </body>
    </html>
  `
  
  printWindow.document.write(receiptHTML)
  printWindow.document.close()
  printWindow.print()
  printWindow.close()
}

async function openShift({ openingCash, notes }) {
  try {
    const response = await api.post(`/cash-registers/1/open-shift`, {
      opening_cash: openingCash,
      opening_notes: notes
    })
    
    currentShift.value = response.data.shift
    showSuccess('✅ Turno abierto correctamente')
    showOpenShift.value = false
    
  } catch (error) {
    showError('❌ Error al abrir el turno')
  }
}

async function closeShift({ closingCash, notes }) {
  try {
    await api.post(`/cash-shifts/${currentShift.value.id}/close`, {
      closing_cash: closingCash,
      closing_notes: notes
    })
    
    currentShift.value = null
    showSuccess('✅ Turno cerrado correctamente')
    showCloseShift.value = false
    
  } catch (error) {
    showError('❌ Error al cerrar el turno')
  }
}

function getCategoryIcon(category) {
  const icons = {
    'todas': '📂',
    'hamburguesas': '🍔',
    'pizzas': '🍕',
    'ensaladas': '🥗',
    'bebidas': '🥤',
    'postres': '🍰'
  }
  return icons[category] || '🍽️'
}

function formatPrice(price) {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return isNaN(numPrice) ? '0.00' : numPrice.toFixed(2)
}

function formatTime(timestamp) {
  return new Date(timestamp).toLocaleTimeString('es-ES')
}

// Lifecycle
onMounted(async () => {
  try {
    // Cargar restaurante
    const restaurantResponse = await api.get(`/restaurants/${restaurantId}`)
    restaurant.value = restaurantResponse.data
    
    // Cargar productos
    const productsResponse = await api.get(`/restaurants/${restaurantId}/products`)
    products.value = productsResponse.data
    
    // Obtener categorías únicas
    const uniqueCategories = [...new Set(products.value.map(p => p.category))]
    categories.value = ['todas', ...uniqueCategories]
    
    // Verificar si hay turno activo
    const shiftsResponse = await api.get('/cash-registers')
    const activeRegister = shiftsResponse.data.find(r => r.currentShift)
    if (activeRegister?.currentShift) {
      currentShift.value = activeRegister.currentShift
    }
    
  } catch (error) {
    showError('❌ Error al cargar datos del POS')
    console.error('Error:', error)
  }
})
</script>

<style scoped>
.pos-container {
  min-height: 100vh;
  background: #f5f5f5;
}

.pos-header {
  background: white;
  padding: 1rem 2rem;
  border-bottom: 1px solid #e1e5e9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.restaurant-info h1 {
  margin: 0 0 0.5rem 0;
  color: #2d3748;
}

.shift-info {
  display: flex;
  gap: 1rem;
  align-items: center;
  font-size: 0.9rem;
}

.shift-badge {
  background: #48bb78;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-weight: 500;
}

.pos-actions {
  display: flex;
  gap: 1rem;
}

.action-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  background: #4299e1;
  color: white;
  cursor: pointer;
  font-weight: 500;
}

.action-btn.danger {
  background: #f56565;
}

.pos-main {
  display: grid;
  grid-template-columns: 2fr 1fr;
  height: calc(100vh - 100px);
}

.products-panel {
  background: white;
  padding: 1rem;
  overflow-y: auto;
}

.search-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  margin-bottom: 1rem;
}

.categories {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.category-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.category-btn.active {
  background: #4299e1;
  color: white;
  border-color: #4299e1;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
}

.product-tile {
  background: #f7fafc;
  border-radius: 8px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.product-tile:hover {
  background: #edf2f7;
  transform: translateY(-2px);
}

.product-image {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 50%;
  margin-bottom: 0.5rem;
}

.product-info h4 {
  margin: 0 0 0.25rem 0;
  font-size: 0.9rem;
}

.product-price {
  font-weight: bold;
  color: #4299e1;
}

.order-panel {
  background: white;
  border-left: 1px solid #e1e5e9;
  padding: 1rem;
  display: flex;
  flex-direction: column;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.clear-btn {
  background: #f56565;
  color: white;
  border: none;
  padding: 0.5rem;
  border-radius: 4px;
  cursor: pointer;
}

.order-items {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 1rem;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.item-info {
  flex: 1;
}

.item-name {
  display: block;
  font-weight: 500;
}

.item-price {
  font-size: 0.9rem;
  color: #718096;
}

.item-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.qty-btn, .remove-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.remove-btn {
  background: #fed7d7;
  border-color: #feb2b2;
}

.order-summary {
  padding: 1rem 0;
  border-top: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 1rem;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.summary-line.total {
  font-size: 1.1rem;
  margin-top: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid #e2e8f0;
}

.customer-section {
  margin-bottom: 1rem;
}

.customer-section h4 {
  margin: 0 0 0.5rem 0;
}

.customer-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  margin-bottom: 0.5rem;
}

.payment-section h4 {
  margin: 0 0 1rem 0;
}

.payment-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
}

.payment-btn {
  padding: 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.9rem;
}

.payment-btn.cash {
  background: #48bb78;
  color: white;
}

.payment-btn.card {
  background: #4299e1;
  color: white;
}

.payment-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.open-shift-btn {
  background: #ed8936;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

@media (max-width: 1024px) {
  .pos-main {
    grid-template-columns: 1fr;
    height: auto;
  }
  
  .products-panel, .order-panel {
    height: auto;
    max-height: 70vh;
    overflow-y: auto;
  }
  
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
  }
}

@media (max-width: 768px) {
  .pos-header {
    padding: 0.75rem 1rem;
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .shift-info {
    width: 100%;
    justify-content: space-between;
  }
  
  .pos-actions {
    width: 100%;
    flex-wrap: wrap;
  }
  
  .action-btn {
    flex-grow: 1;
    text-align: center;
  }
  
  .categories {
    overflow-x: auto;
    padding-bottom: 0.5rem;
    flex-wrap: nowrap;
  }
  
  .category-btn {
    white-space: nowrap;
  }
  
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }
}

@media (max-width: 480px) {
  .pos-header {
    padding: 0.75rem;
  }
  
  .restaurant-info h1 {
    font-size: 1.2rem;
  }
  
  .products-panel {
    padding: 0.75rem;
  }
  
  .products-grid {
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
  }
  
  .product-tile {
    padding: 0.75rem;
  }
  
  .product-image {
    width: 50px;
    height: 50px;
  }
  
  .product-info h4 {
    font-size: 0.8rem;
  }
  
  .order-panel {
    padding: 0.75rem;
  }
  
  .payment-buttons {
    grid-template-columns: 1fr;
  }
}
</style>