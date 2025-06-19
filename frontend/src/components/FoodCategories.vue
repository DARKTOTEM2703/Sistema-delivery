<template>
  <div class="categories-container">
    <div 
      v-for="category in categories" 
      :key="category.id"
      class="category-item"
      :class="{ active: activeCategory === category.id }"
      @click="setActiveCategory(category.id)"
    >
      <div class="category-icon">{{ category.icon }}</div>
      <div class="category-name">{{ category.name }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const categories = ref([
  { id: 'pizzas', name: 'Pizzas', icon: '🍕' },
  { id: 'hamburguesas', name: 'Hamburguesas', icon: '🍔' },
  { id: 'ensaladas', name: 'Ensaladas', icon: '🥗' },
  { id: 'pastas', name: 'Pastas', icon: '🍝' }
]);

const activeCategory = ref('hamburguesas');
const emit = defineEmits(['change-category']);

function setActiveCategory(categoryId) {
  activeCategory.value = categoryId;
  emit('change-category', categoryId);
}
</script>

<style scoped>
.categories-container {
  display: flex;
  justify-content: space-around;
  padding: 1rem 0;
  background-color: var(--card-bg);
  border-radius: 8px;
  margin-bottom: 2rem;
  box-shadow: var(--box-shadow);
  border: 1px solid var(--border-color);
}

.category-item {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  color: var(--text-color);
}

.category-item:hover {
  background-color: rgba(128, 128, 128, 0.1);
}

.category-item.active {
  background: var(--button-primary);
  color: white;
}

.category-icon {
  font-size: 1.5rem;
}

.category-name {
  font-weight: 500;
}

@media (max-width: 768px) {
  .categories-container {
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  
  .category-item {
    padding: 0.5rem 1rem;
  }
}
</style>