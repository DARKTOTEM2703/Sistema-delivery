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
  // HAMBURGUESAS
  {
    id: 1,
    category: 'hamburguesas',
    name: 'Hamburguesa Clásica',
    price: 15.99,
    description: 'Carne de res, lechuga, tomate, cebolla, pepinillos y salsa especial',
    rating: 4.7,
    time: '10-15 min',
    image: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTB8fGJ1cmdlcnxlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 2,
    category: 'hamburguesas',
    name: 'Hamburguesa BBQ',
    price: 18.99,
    description: 'Carne de res, bacon, cebolla caramelizada, queso cheddar y salsa BBQ',
    rating: 4.8,
    time: '12-18 min',
    image: 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8YnVyZ2VyfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 9,
    category: 'hamburguesas',
    name: 'Hamburguesa Doble Queso',
    price: 19.99,
    description: 'Doble carne de res, triple queso cheddar, pepinillos y salsa secreta',
    rating: 4.9,
    time: '15-20 min',
    image: 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 10,
    category: 'hamburguesas',
    name: 'Hamburguesa Vegana',
    price: 17.99,
    description: 'Carne vegetal a base de plantas, lechuga, tomate y salsa vegana especial',
    rating: 4.6,
    time: '10-15 min',
    image: 'https://images.unsplash.com/photo-1585238342024-78d387f4a707?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 11,
    category: 'hamburguesas',
    name: 'Hamburguesa Mexicana',
    price: 20.99,
    description: 'Carne de res, guacamole, jalapeños, queso pepper jack y salsa picante',
    rating: 4.7,
    time: '12-18 min',
    image: 'https://images.unsplash.com/photo-1594212699903-ec8a3eca50f5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 12,
    category: 'hamburguesas',
    name: 'Hamburguesa Gourmet',
    price: 22.99,
    description: 'Carne angus, queso brie, rúcula, cebolla caramelizada y salsa de trufa',
    rating: 4.9,
    time: '15-20 min',
    image: 'https://images.unsplash.com/photo-1596662951482-0c4ba74a6df6?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  
  // PIZZAS
  {
    id: 3,
    category: 'pizzas',
    name: 'Pizza Margherita',
    price: 18.99,
    description: 'Salsa de tomate, mozzarella fresca, albahaca y aceite de oliva',
    rating: 4.8,
    time: '15-20 min',
    servings: '2 pers.',
    image: 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NXx8cGl6emF8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 4,
    category: 'pizzas',
    name: 'Pizza Pepperoni',
    price: 21.99,
    description: 'Salsa de tomate, mozzarella, pepperoni y orégano',
    rating: 4.9,
    time: '15-20 min',
    servings: '2 pers.',
    image: 'https://images.unsplash.com/photo-1628840042765-356cda07504e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTh8fHBpenphfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 13,
    category: 'pizzas',
    name: 'Pizza Hawaiana',
    price: 20.99,
    description: 'Salsa de tomate, mozzarella, jamón y piña',
    rating: 4.5,
    time: '15-20 min',
    servings: '2 pers.',
    image: 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 14,
    category: 'pizzas',
    name: 'Pizza Cuatro Quesos',
    price: 22.99,
    description: 'Mozzarella, gorgonzola, parmesano y queso de cabra',
    rating: 4.7,
    time: '15-20 min',
    servings: '2 pers.',
    image: 'https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 15,
    category: 'pizzas',
    name: 'Pizza Vegetariana',
    price: 20.99,
    description: 'Salsa de tomate, mozzarella, pimientos, cebolla, champiñones y aceitunas',
    rating: 4.6,
    time: '18-25 min',
    servings: '2 pers.',
    image: 'https://images.unsplash.com/photo-1593560708920-61dd98c46a4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 16,
    category: 'pizzas',
    name: 'Pizza Barbacoa',
    price: 23.99,
    description: 'Salsa barbacoa, mozzarella, pollo, bacon y cebolla',
    rating: 4.8,
    time: '15-20 min',
    servings: '2 pers.',
    image: 'https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  
  // ENSALADAS
  {
    id: 5,
    category: 'ensaladas',
    name: 'Ensalada César',
    price: 12.99,
    description: 'Lechuga romana, crutones, parmesano y aderezo César',
    rating: 4.5,
    time: '5-10 min',
    image: 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8Y2Flc2FyJTIwc2FsYWR8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 6,
    category: 'ensaladas',
    name: 'Ensalada Mediterránea',
    price: 14.99,
    description: 'Lechuga, tomate, pepino, aceitunas, queso feta y aderezo de limón',
    rating: 4.6,
    time: '5-10 min',
    image: 'https://images.unsplash.com/photo-1540420773420-3366772f4999?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8c2FsYWR8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 17,
    category: 'ensaladas',
    name: 'Ensalada de Quinoa',
    price: 15.99,
    description: 'Quinoa, aguacate, tomate cherry, espinacas y vinagreta de limón',
    rating: 4.7,
    time: '8-12 min',
    image: 'https://images.unsplash.com/photo-1505253716362-afaea1d3d1af?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 18,
    category: 'ensaladas',
    name: 'Ensalada Waldorf',
    price: 13.99,
    description: 'Manzanas, apio, nueces, uvas y aderezo de mayonesa',
    rating: 4.4,
    time: '5-10 min',
    image: 'https://images.unsplash.com/photo-1607532941433-304659e8198a?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 19,
    category: 'ensaladas',
    name: 'Ensalada de Pollo',
    price: 16.99,
    description: 'Pollo a la parrilla, lechuga romana, parmesano, crutones y aderezo César',
    rating: 4.8,
    time: '8-12 min',
    image: 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 20,
    category: 'ensaladas',
    name: 'Ensalada de Salmón',
    price: 18.99,
    description: 'Salmón ahumado, espinacas, aguacate, pepino y vinagreta de eneldo',
    rating: 4.9,
    time: '8-12 min',
    image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  
  // PASTAS
  {
    id: 7,
    category: 'pastas',
    name: 'Espaguetis a la Bolognesa',
    price: 16.99,
    description: 'Espaguetis con salsa de carne, tomate y hierbas',
    rating: 4.7,
    time: '15-20 min',
    image: 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8M3x8c3BhZ2hldHRpfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 8,
    category: 'pastas',
    name: 'Fettuccine Alfredo',
    price: 17.99,
    description: 'Fettuccine con salsa cremosa de queso parmesano y mantequilla',
    rating: 4.8,
    time: '15-20 min',
    image: 'https://images.unsplash.com/photo-1608219992759-8d74ed8d76eb?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8cGFzdGElMjBhbGZyZWRvfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 21,
    category: 'pastas',
    name: 'Lasaña de Carne',
    price: 18.99,
    description: 'Capas de pasta con salsa boloñesa, bechamel y queso gratinado',
    rating: 4.9,
    time: '20-25 min',
    image: 'https://images.unsplash.com/photo-1574894709920-11b28e7367e3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 22,
    category: 'pastas',
    name: 'Raviolis de Queso',
    price: 18.99,
    description: 'Raviolis rellenos de ricotta y espinacas con salsa de tomate',
    rating: 4.6,
    time: '15-20 min',
    image: 'https://images.unsplash.com/photo-1587740908075-9e245715d5e5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 23,
    category: 'pastas',
    name: 'Penne Arrabiata',
    price: 15.99,
    description: 'Pasta penne con salsa de tomate picante, ajo y perejil',
    rating: 4.5,
    time: '12-18 min',
    image: 'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 24,
    category: 'pastas',
    name: 'Linguine al Pesto',
    price: 17.99,
    description: 'Linguine con salsa pesto de albahaca, piñones y parmesano',
    rating: 4.7,
    time: '12-18 min',
    image: 'https://images.unsplash.com/photo-1611270629569-8b357cb88da9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  
  // BEBIDAS
  {
    id: 25,
    category: 'bebidas',
    name: 'Limonada Casera',
    price: 4.99,
    description: 'Limonada fresca con menta y hielo',
    rating: 4.7,
    time: '3-5 min',
    image: 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 26,
    category: 'bebidas',
    name: 'Smoothie de Frutas',
    price: 6.99,
    description: 'Batido de fresa, plátano y mango',
    rating: 4.8,
    time: '5-8 min',
    image: 'https://images.unsplash.com/photo-1589733955941-5eeaf752f6dd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 27,
    category: 'bebidas',
    name: 'Agua Mineral',
    price: 2.99,
    description: 'Agua con o sin gas',
    rating: 4.5,
    time: '2 min',
    image: 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 28,
    category: 'bebidas',
    name: 'Cerveza Artesanal',
    price: 7.99,
    description: 'Cerveza local de producción artesanal',
    rating: 4.6,
    time: '3-5 min',
    image: 'https://images.unsplash.com/photo-1566633806327-68e152aaf26d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  
  // POSTRES
  {
    id: 29,
    category: 'postres',
    name: 'Tiramisú',
    price: 8.99,
    description: 'Postre italiano con café, mascarpone y cacao',
    rating: 4.9,
    time: '5 min',
    image: 'https://images.unsplash.com/photo-1571877899707-581a8660c850?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 30,
    category: 'postres',
    name: 'Cheesecake',
    price: 7.99,
    description: 'Tarta de queso con base de galleta y mermelada de frutos rojos',
    rating: 4.8,
    time: '5 min',
    image: 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 31,
    category: 'postres',
    name: 'Brownie con Helado',
    price: 9.99,
    description: 'Brownie caliente con helado de vainilla y salsa de chocolate',
    rating: 4.9,
    time: '8-10 min',
    image: 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  },
  {
    id: 32,
    category: 'postres',
    name: 'Crème Brûlée',
    price: 8.99,
    description: 'Crema pastelera con cobertura de azúcar caramelizado',
    rating: 4.7,
    time: '5 min',
    image: 'https://images.unsplash.com/photo-1630356221238-7b7645c60238?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
  }
]);

const filteredProducts = computed(() => {
  let filtered = products.value;
  
  // Filtrar por categoría si hay una seleccionada
  if (activeCategory.value) {
    filtered = filtered.filter(product => product.category === activeCategory.value);
  }
  
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

/* Estilos para el componente ProductCard */
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

/* Contenedor limitado para la imagen */
.product-image-container {
  width: 100%;
  height: 180px; /* Altura fija más pequeña */
  overflow: hidden;
  position: relative;
  background-color: rgba(128, 128, 128, 0.1);
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Mantiene la proporción y cubre el área */
  transition: transform 0.3s;
}

/* Efecto hover opcional para la imagen */
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
  /* Aseguramos que el texto no se corte */
  display: -webkit-box;
  -webkit-line-clamp: 3; /* Limita a 3 líneas */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
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