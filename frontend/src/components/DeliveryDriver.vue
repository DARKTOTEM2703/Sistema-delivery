<!-- filepath: c:\xampp\htdocs\Sistema-delivery\frontend\src\components\DeliveryDriver.vue -->
<template>
  <div class="driver-card" v-if="driver">
    <div class="driver-header">
      <h3>Tu repartidor</h3>
      <div class="status-indicator">En camino</div>
    </div>
    
    <div class="driver-content">
      <div class="driver-avatar">
        <img :src="driver?.avatar || '/img/avatar-placeholder.png'" alt="Foto del repartidor">
      </div>
      
      <div class="driver-info">
        <h4>{{ driver.name }}</h4>
        <div class="rating">
          <span class="stars">★★★★★</span>
          <span class="rating-value">{{ driver.rating || '4.8' }}</span>
        </div>
        <p class="vehicle-info" v-if="driver.vehicle_info">{{ driver.vehicle_info }}</p>
      </div>
    </div>
    
    <div class="contact-actions">
      <button class="btn-call" @click="callDriver">
        <span class="icon">📞</span>
        <span>Llamar</span>
      </button>
      
      <button class="btn-message" @click="messageDriver">
        <span class="icon">💬</span>
        <span>Mensaje</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps } from 'vue';

const props = defineProps({
  driver: {
    type: Object,
    default: () => null
  },
  orderId: {
    type: [Number, String],
    required: true
  }
});

function callDriver() {
  if (props.driver?.phone) {
    window.location.href = `tel:${props.driver.phone}`;
  }
}

function messageDriver() {
  if (props.driver?.phone) {
    window.location.href = `sms:${props.driver.phone}`;
  }
}
</script>

<style scoped>
.driver-card {
  background-color: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  margin-bottom: 1.5rem;
}

.driver-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.driver-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.status-indicator {
  background-color: #eef2ff;
  color: #6366f1;
  padding: 0.25rem 0.75rem;
  border-radius: 100px;
  font-size: 0.75rem;
  font-weight: 500;
}

.driver-content {
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;
}

.driver-avatar {
  margin-right: 1rem;
}

.driver-avatar img {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #3b82f6;
}

.driver-info h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1.125rem;
}

.rating {
  display: flex;
  align-items: center;
  margin-bottom: 0.25rem;
}

.stars {
  color: #fbbf24;
  margin-right: 0.5rem;
  letter-spacing: -1px;
}

.rating-value {
  font-weight: 600;
}

.vehicle-info {
  margin: 0.25rem 0 0 0;
  font-size: 0.875rem;
  color: #6b7280;
}

.contact-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.btn-call, .btn-message {
  padding: 0.75rem;
  border-radius: 8px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-call {
  background-color: #ecfdf5;
  color: #10b981;
}

.btn-call:hover {
  background-color: #d1fae5;
}

.btn-message {
  background-color: #eff6ff;
  color: #3b82f6;
}

.btn-message:hover {
  background-color: #dbeafe;
}

.icon {
  margin-right: 0.5rem;
}

@media (max-width: 768px) {
  .driver-card {
    padding: 1.25rem;
  }
  
  .contact-actions {
    gap: 0.5rem;
  }
}

@media (max-width: 480px) {
  .driver-card {
    padding: 1rem;
  }
  
  .driver-avatar img {
    width: 50px;
    height: 50px;
  }
  
  .driver-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .status-indicator {
    align-self: flex-start;
  }
  
  .contact-actions {
    flex-direction: column;
  }
  
  .btn-call, .btn-message {
    width: 100%;
  }
}
</style>