<template>
  <div class="product-card">
    <!-- Contenedor para la imagen con tamaño fijo -->
    <div class="product-image-container">
      <img 
        v-if="product.image" 
        :src="product.image" 
        :alt="product.name" 
        class="product-image"
        loading="lazy"
      >
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
  background-color: var(--card-bg);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--box-shadow);
  height: 100%;
  transition: transform 0.3s, box-shadow 0.3s;
  border: 1px solid var(--border-color);
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

/* Contenedor con dimensiones fijas para la imagen */
.product-image-container {
  width: 100%;
  height: 200px; /* Altura fija para todas las imágenes */
  overflow: hidden;
  position: relative;
  background-color: rgba(128, 128, 128, 0.1);
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Mantiene la proporción y cubre el área */
  object-position: center; /* Centra el contenido de la imagen */
  transition: transform 0.3s;
}

/* Efecto hover para la imagen */
.product-image-container:hover .product-image {
  transform: scale(1.05);
}

.placeholder-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.placeholder-image span {
  font-size: 3rem;
  color: var(--border-color);
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
  color: var(--text-color);
}

.product-price {
  font-size: 1.25rem;
  font-weight: 600;
  color: #ff6b00;
}

.product-description {
  color: var(--text-color);
  opacity: 0.8;
  margin-bottom: 1rem;
  line-height: 1.4;
  /* Truncar texto largo */
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  height: 4.2em; /* Altura fija para 3 líneas */
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
  color: var(--text-color);
  opacity: 0.7;
  font-size: 0.9rem;
}

.add-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: var(--button-primary);
  color: white;
  border: none;
  border-radius: 6px;
  padding: 0.75rem 1.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  margin-top: auto;
  width: fit-content;
}

.add-button:hover {
  transform: translateY(-2px);
  box-shadow: var(--box-shadow);
  background: var(--button-hover);
}
</style>