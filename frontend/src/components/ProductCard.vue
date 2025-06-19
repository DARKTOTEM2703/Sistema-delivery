<template>
  <div class="product-card">
    <div class="product-image">
      <img v-if="product.image" :src="product.image" :alt="product.name">
      <div v-else class="placeholder-image">
        <span>📷</span>
      </div>
    </div>
    <div class="product-info">
      <div class="product-header">
        <h3 class="product-name">{{ product.name }}</h3>
        <span class="product-price">${{ product.price.toFixed(2) }}</span>
      </div>
      <p class="product-description">{{ product.description }}</p>
      <div class="product-meta">
        <div class="rating">
          <span class="star">⭐</span>
          <span>{{ product.rating }}</span>
        </div>
        <div class="time">
          <span class="clock">🕒</span>
          <span>{{ product.time }}</span>
        </div>
        <div v-if="product.servings" class="servings">
          <span class="person">👤</span>
          <span>{{ product.servings }}</span>
        </div>
      </div>
      <button class="add-button" @click="addToCart">
        <span class="plus-icon">+</span>
        Agregar
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  product: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['add-to-cart']);

function addToCart() {
  emit('add-to-cart', props.product);
}
</script>

<style scoped>
.product-card {
  display: flex;
  flex-direction: column;
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  height: 100%;
}

.product-image, .placeholder-image {
  width: 100%;
  height: 200px;
  background-color: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.placeholder-image span {
  font-size: 3rem;
  color: #ccc;
}

.product-info {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.product-name {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.product-price {
  font-size: 1.25rem;
  font-weight: 600;
  color: #333;
}

.product-description {
  color: #666;
  margin-bottom: 1rem;
  line-height: 1.4;
}

.product-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  align-items: center;
}

.rating, .time, .servings {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #666;
  font-size: 0.9rem;
}

.add-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background-color: #222;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 0.75rem 1.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s;
  margin-top: auto;
  width: fit-content;
}

.add-button:hover {
  background-color: #000;
}
</style>