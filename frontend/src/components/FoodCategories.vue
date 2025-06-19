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
  background-color: #f9f9f9;
  border-radius: 8px;
  margin-bottom: 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
}

.category-item:hover {
  background-color: #f0f0f0;
}

.category-item.active {
  background-color: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.category-icon {
  font-size: 1.5rem;
}

.category-name {
  font-weight: 500;
}
</style>