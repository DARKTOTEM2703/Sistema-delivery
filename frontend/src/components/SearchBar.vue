<template>
  <div class="search-container">
    <input 
      type="text" 
      v-model="searchQuery" 
      @input="search"
      placeholder="Buscar productos..." 
      class="search-input"
    />
    <button class="search-button">
      🔍
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const searchQuery = ref('');
const emit = defineEmits(['search']);

function search() {
  emit('search', searchQuery.value);
}
</script>

<style scoped>
.search-container {
  display: flex;
  width: 100%;
  max-width: 500px;
  margin: 0 auto 1.5rem;
}

.search-input {
  flex: 1;
  padding: 10px 15px;
  border: 1px solid var(--border-color);
  border-radius: 6px 0 0 6px;
  font-size: 1rem;
  background: var(--card-bg);
  color: var(--text-color);
}

.search-button {
  padding: 10px 15px;
  background: var(--button-primary);
  color: white;
  border: none;
  border-radius: 0 6px 6px 0;
  cursor: pointer;
}
</style>