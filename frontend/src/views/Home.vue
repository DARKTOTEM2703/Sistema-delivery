<!-- filepath: frontend/src/views/Home.vue -->
<template>
  <div>
    <hero />
    <main class="main-container">
      <SearchBar @search="handleSearch" />
      <ProductList 
        @add-to-cart="handleAddToCart" 
        :searchQuery="searchQuery"
      />
    </main>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import hero from '../components/hero.vue';
import ProductList from '../components/ProductList.vue';
import SearchBar from '../components/SearchBar.vue';

const searchQuery = ref('');

// Inyectar funciones del carrito
const { addToCart } = inject('cart');

// Emitir evento para que el padre (App.vue) maneje
const emit = defineEmits(['add-to-cart']);

function handleSearch(query) {
  searchQuery.value = query;
}

function handleAddToCart(product) {
  // Usar la función global del carrito
  addToCart(product);
  // También emitir el evento para compatibilidad
  emit('add-to-cart', product);
}
</script>

<style scoped>
.main-container {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 0 15px;
}
</style>