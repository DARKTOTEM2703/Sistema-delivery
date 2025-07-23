<template>
  <div class="restaurant-card" :class="{ closed: !restaurant.is_open }" @click="$emit('select-restaurant', restaurant)">
    <div class="restaurant-image">
      <img 
        :src="restaurant.logo || '/img/restaurant-placeholder.jpg'" 
        :alt="restaurant.name"
        loading="lazy"
      >
      <div class="restaurant-status" :class="restaurant.is_open ? 'status-open' : 'status-closed'">
        {{ restaurant.is_open ? 'ABIERTO' : 'CERRADO' }}
      </div>
      <div class="restaurant-category">{{ restaurant.category }}</div>
    </div>
    
    <div class="restaurant-content">
      <div class="restaurant-header">
        <h3 class="restaurant-name">{{ restaurant.name }}</h3>
        <div class="rating">
          ⭐ {{ restaurant.rating }}
          <span class="review-count">({{ restaurant.total_reviews }})</span>
        </div>
      </div>
      
      <p class="description">{{ restaurant.description }}</p>
      
      <div class="restaurant-meta">
        <div class="meta-item delivery-time">
          <span class="icon">🚚</span>
          <span>{{ restaurant.delivery_time_min }}-{{ restaurant.delivery_time_max }} min</span>
        </div>
        <div class="meta-item delivery-fee">
          <span class="icon">💰</span>
          <span>Envío: ${{ restaurant.delivery_fee }}</span>
        </div>
        <div class="meta-item minimum-order">
          <span class="icon">📦</span>
          <span>Mínimo: ${{ restaurant.minimum_order }}</span>
        </div>
      </div>

      <div class="restaurant-actions">
        <button 
          class="view-menu-btn" 
          :disabled="!restaurant.is_open"
          @click.stop="viewMenu"
        >
          {{ restaurant.is_open ? 'Ver Menú' : 'Cerrado' }}
        </button>
        <button class="favorite-btn" @click.stop="toggleFavorite">
          {{ isFavorite ? '❤️' : '🤍' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  restaurant: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['select-restaurant', 'view-menu', 'toggle-favorite']);

const isFavorite = ref(false); // Esto vendría de un store o API

function viewMenu() {
  emit('view-menu', props.restaurant);
}

function toggleFavorite() {
  isFavorite.value = !isFavorite.value;
  emit('toggle-favorite', props.restaurant);
}
</script>

<style scoped>
.restaurant-card {
  background-color: var(--card-bg);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s, box-shadow 0.2s;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.restaurant-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.restaurant-card.closed {
  opacity: 0.75;
}

.restaurant-image {
  height: 160px;
  width: 100%;
  object-fit: cover;
}

.restaurant-status {
  position: absolute;
  top: 10px;
  right: 10px;
  padding: 4px 10px;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 100px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-open {
  background-color: rgba(16, 185, 129, 0.9);
  color: white;
}

.status-closed {
  background-color: rgba(239, 68, 68, 0.9);
  color: white;
}

.restaurant-category {
  position: absolute;
  bottom: 10px;
  left: 10px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 4px 8px;
  border-radius: 8px;
  font-size: 0.8rem;
  text-transform: capitalize;
}

.restaurant-content {
  padding: 1rem;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.restaurant-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.restaurant-name {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 0.5rem;
  color: var(--text-color);
}

.rating {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: 600;
  color: var(--text-color);
}

.review-count {
  font-size: 0.8rem;
  opacity: 0.7;
  font-weight: normal;
}

.description {
  color: var(--text-color);
  opacity: 0.8;
  margin-bottom: 1rem;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.restaurant-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.85rem;
  color: #666;
}

.restaurant-actions {
  margin-top: auto;
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
}

.view-menu-btn {
  flex: 1;
  background: var(--button-primary);
  border: none;
  color: white;
  padding: 0.6rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: opacity 0.2s;
}

.view-menu-btn:hover {
  opacity: 0.9;
}

.view-menu-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
}

.favorite-btn {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  cursor: pointer;
  color: #666;
  transition: all 0.2s;
}

.favorite-btn.active {
  color: #ff3b30;
  background-color: rgba(255, 59, 48, 0.1);
}

.rating-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 3px 8px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 3px;
}

/* Media queries para responsividad */
@media (max-width: 768px) {
  .restaurant-image {
    height: 140px;
  }
  
  .restaurant-name {
    font-size: 1rem;
  }
  
  .restaurant-content {
    padding: 0.75rem;
  }
}

@media (max-width: 480px) {
  .restaurant-card {
    border-radius: 10px;
  }
  
  .meta-item {
    font-size: 0.8rem;
  }
  
  .restaurant-actions {
    flex-direction: column;
  }
  
  .view-menu-btn, .favorite-btn {
    width: 100%;
    height: 38px;
  }
}
</style>