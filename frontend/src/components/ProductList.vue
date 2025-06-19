<template>
  <div>
    <FoodCategories @change-category="changeCategory" />
    
    <div class="products-grid">
      <ProductCard 
        v-for="product in filteredProducts" 
        :key="product.id" 
        :product="product"
        @add-to-cart="handleAddToCart"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import FoodCategories from './FoodCategories.vue';
import ProductCard from './ProductCard.vue';

const activeCategory = ref('hamburguesas');
const emit = defineEmits(['add-to-cart']);

const props = defineProps({
  searchQuery: {
    type: String,
    default: ''
  }
});

const products = ref([
  {
    id: 1,
    category: 'hamburguesas',
    name: 'Hamburguesa Clásica',
    price: 15.99,
    description: 'Carne de res, lechuga, tomate, cebolla, pepinillos y salsa especial',
    rating: 4.7,
    time: '10-15 min'
  },
  {
    id: 2,
    category: 'hamburguesas',
    name: 'Hamburguesa BBQ',
    price: 18.99,
    description: 'Carne de res, bacon, cebolla caramelizada, queso cheddar y salsa BBQ',
    rating: 4.8,
    time: '12-18 min'
  },
  {
    id: 3,
    category: 'pizzas',
    name: 'Pizza Margherita',
    price: 18.99,
    description: 'Salsa de tomate, mozzarella fresca, albahaca y aceite de oliva',
    rating: 4.8,
    time: '15-20 min',
    servings: '2 pers.'
  },
  {
    id: 4,
    category: 'pizzas',
    name: 'Pizza Pepperoni',
    price: 21.99,
    description: 'Salsa de tomate, mozzarella, pepperoni y orégano',
    rating: 4.9,
    time: '15-20 min',
    servings: '2 pers.'
  },
   {
    id: 5,
    category: 'ensaladas',
    name: 'Ensalada César',
    price: 12.99,
    description: 'Lechuga romana, crutones, parmesano y aderezo César',
    rating: 4.5,
    time: '5-10 min'
  },
  {
    id: 6,
    category: 'ensaladas',
    name: 'Ensalada Mediterránea',
    price: 14.99,
    description: 'Lechuga, tomate, pepino, aceitunas, queso feta y aderezo de limón',
    rating: 4.6,
    time: '5-10 min'
  },
   {
    id: 7,
    category: 'pastas',
    name: 'Espaguetis a la Bolognesa',
    price: 16.99,
    description: 'Espaguetis con salsa de carne, tomate y hierbas',
    rating: 4.7,
    time: '15-20 min'
  },
  {
    id: 8,
    category: 'pastas',
    name: 'Fettuccine Alfredo',
    price: 17.99,
    description: 'Fettuccine con salsa cremosa de queso parmesano y mantequilla',
    rating: 4.8,
    time: '15-20 min'
  }
]);

const filteredProducts = computed(() => {
  let filtered = products.value.filter(product => product.category === activeCategory.value);
  
  // Si hay un término de búsqueda, filtrar por nombre o descripción
  if (props.searchQuery) {
    const searchTerm = props.searchQuery.toLowerCase();
    filtered = filtered.filter(product => 
      product.name.toLowerCase().includes(searchTerm) || 
      product.description.toLowerCase().includes(searchTerm)
    );
  }
  
  return filtered;
});

function changeCategory(category) {
  activeCategory.value = category;
}

function handleAddToCart(product) {
  emit('add-to-cart', product);
}
</script>

<style scoped>
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
  padding: 1rem 0;
}

@media (max-width: 768px) {
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
  }
}
</style>