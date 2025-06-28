import axios from "axios";

const api = axios.create({
  baseURL: "http://localhost:8000/api",
});

// Interceptor para incluir token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default {
  // Productos (YA FUNCIONA)
  async getProducts() {
    return await api.get("/products");
  },

  async getProduct(id) {
    return await api.get(`/products/${id}`);
  },

  // RESTAURANTES
  async getRestaurants() {
    try {
      return await api.get("/restaurants");
    } catch (error) {
      // Fallback si no existe el endpoint
      return {
        data: [
          {
            id: 1,
            name: 'Pizzería Italiana',
            description: 'Auténtica pizza italiana con ingredientes frescos',
            category: 'italiana',
            rating: 4.8,
            total_reviews: 156,
            delivery_time_min: 25,
            delivery_time_max: 35,
            delivery_fee: 3.50,
            minimum_order: 15.00,
            is_open_now: true,
            cover_image: 'https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
          },
          {
            id: 2,
            name: 'Burger House',
            description: 'Las mejores hamburguesas gourmet de la ciudad',
            category: 'americana',
            rating: 4.6,
            total_reviews: 89,
            delivery_time_min: 20,
            delivery_time_max: 30,
            delivery_fee: 2.99,
            minimum_order: 12.00,
            is_open_now: true,
            cover_image: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
          },
          {
            id: 3,
            name: 'Sushi Zen',
            description: 'Sushi fresco y rollos especiales',
            category: 'japonesa',
            rating: 4.9,
            total_reviews: 203,
            delivery_time_min: 30,
            delivery_time_max: 45,
            delivery_fee: 4.99,
            minimum_order: 20.00,
            is_open_now: false,
            cover_image: 'https://images.unsplash.com/photo-1553621042-f6e147245754?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
          }
        ]
      };
    }
  },

  async getRestaurant(id) {
    try {
      return await api.get(`/restaurants/${id}`);
    } catch (error) {
      // Fallback para restaurante individual
      const fallbackRestaurants = {
        1: {
          id: 1,
          name: 'Pizzería Italiana',
          description: 'Auténtica pizza italiana con ingredientes frescos',
          rating: 4.8,
          total_reviews: 156,
          delivery_time_min: 25,
          delivery_time_max: 35,
          delivery_fee: 3.50,
          cover_image: 'https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
        },
        2: {
          id: 2,
          name: 'Burger House',
          description: 'Las mejores hamburguesas gourmet de la ciudad',
          rating: 4.6,
          total_reviews: 89,
          delivery_time_min: 20,
          delivery_time_max: 30,
          delivery_fee: 2.99,
          cover_image: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'
        }
      };
      
      return {
        data: fallbackRestaurants[id] || fallbackRestaurants[1]
      };
    }
  },

  async getProductsByRestaurant(restaurantId) {
    try {
      return await api.get(`/restaurants/${restaurantId}/products`);
    } catch (error) {
      // Fallback para productos del restaurante
      const fallbackProducts = [
        {
          id: 1,
          name: 'Pizza Margherita',
          description: 'Salsa de tomate, mozzarella fresca, albahaca',
          price: 18.99,
          image: 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
          restaurant_id: parseInt(restaurantId),
          rating: 4.8,
          time: '15-20 min'
        },
        {
          id: 2,
          name: 'Pizza Pepperoni',
          description: 'Salsa de tomate, mozzarella, pepperoni premium',
          price: 21.99,
          image: 'https://images.unsplash.com/photo-1628840042765-356cda07504e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60',
          restaurant_id: parseInt(restaurantId),
          rating: 4.7,
          time: '15-20 min'
        }
      ];
      
      return { data: fallbackProducts };
    }
  },

  async getRestaurantCategories() {
    try {
      return await api.get("/restaurants/categories");
    } catch (error) {
      return {
        data: ['italiana', 'americana', 'japonesa', 'mexicana', 'saludable']
      };
    }
  },

  // Autenticación (YA FUNCIONA)
  async login(credentials) {
    return await api.post("/login", credentials);
  },

  async register(userData) {
    return await api.post("/register", userData);
  },

  async logout() {
    return await api.post("/logout");
  },

  // Pedidos (YA FUNCIONA)
  async createOrder(orderData) {
    return await api.post("/orders", orderData);
  },

  // Roles (PREPARADO PARA EL FUTURO)
  async getUserRoles() {
    return await api.get("/user/roles");
  },

  async switchRole(roleData) {
    return await api.post("/user/switch-role", roleData);
  },
};
