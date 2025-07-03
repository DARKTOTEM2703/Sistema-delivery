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

  // PRODUCTOS
  async createProduct(productData) {
    return await api.post("/products", productData);
  },

  async updateProduct(id, productData) {
    return await api.put(`/products/${id}`, productData);
  },

  async deleteProduct(id) {
    return await api.delete(`/products/${id}`);
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
            name: "Pizzería Italiana",
            description: "Auténtica pizza italiana con ingredientes frescos",
            category: "italiana",
            rating: 4.8,
            total_reviews: 156,
            delivery_time_min: 25,
            delivery_time_max: 35,
            delivery_fee: 3.5,
            minimum_order: 15.0,
            is_active: true,
            cover_image:
              "https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60",
          },
          {
            id: 2,
            name: "Burger House",
            description: "Las mejores hamburguesas gourmet de la ciudad",
            category: "americana",
            rating: 4.6,
            total_reviews: 89,
            delivery_time_min: 20,
            delivery_time_max: 30,
            delivery_fee: 2.99,
            minimum_order: 12.0,
            is_active: true,
            cover_image:
              "https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60",
          },
        ],
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
          name: "Pizzería Italiana",
          description: "Auténtica pizza italiana con ingredientes frescos",
          rating: 4.8,
          total_reviews: 156,
          delivery_time_min: 25,
          delivery_time_max: 35,
          delivery_fee: 3.5,
          cover_image:
            "https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60",
        },
        2: {
          id: 2,
          name: "Burger House",
          description: "Las mejores hamburguesas gourmet de la ciudad",
          rating: 4.6,
          total_reviews: 89,
          delivery_time_min: 20,
          delivery_time_max: 30,
          delivery_fee: 2.99,
          cover_image:
            "https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60",
        },
      };

      return {
        data: fallbackRestaurants[id] || fallbackRestaurants[1],
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
          name: "Pizza Margherita",
          description: "Salsa de tomate, mozzarella fresca, albahaca",
          price: 18.99,
          image:
            "https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60",
          restaurant_id: parseInt(restaurantId),
          rating: 4.8,
          time: "15-20 min",
        },
        {
          id: 2,
          name: "Pizza Pepperoni",
          description: "Salsa de tomate, mozzarella, pepperoni premium",
          price: 21.99,
          image:
            "https://images.unsplash.com/photo-1628840042765-356cda07504e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60",
          restaurant_id: parseInt(restaurantId),
          rating: 4.7,
          time: "15-20 min",
        },
      ];

      return { data: fallbackProducts };
    }
  },

  async getRestaurantCategories() {
    try {
      return await api.get("/restaurants/categories");
    } catch (error) {
      return {
        data: ["italiana", "americana", "japonesa", "mexicana", "saludable"],
      };
    }
  },

  // Autenticación (COMPLETAMENTE IMPLEMENTADA)
  async login(credentials) {
    try {
      console.log("🔑 Intentando login:", credentials);
      const response = await api.post("/login", credentials);
      console.log("✅ Login exitoso:", response.data);
      return response;
    } catch (error) {
      console.error(
        "❌ Error en login:",
        error.response?.data || error.message
      );
      throw error;
    }
  },

  async register(userData) {
    try {
      console.log("📝 Intentando registro:", userData);
      const response = await api.post("/register", userData);
      console.log("✅ Registro exitoso:", response.data);
      return response;
    } catch (error) {
      console.error(
        "❌ Error en registro:",
        error.response?.data || error.message
      );
      throw error;
    }
  },

  async logout() {
    try {
      const response = await api.post("/logout");
      return response;
    } catch (error) {
      console.error("❌ Error en logout:", error);
      // No lanzar error porque el logout local debe funcionar
      return null;
    }
  },

  // Pedidos (YA FUNCIONA)
  async createOrder(orderData) {
    // ✅ ASEGURAR QUE orderData incluya restaurant_id
    const requiredData = {
      restaurant_id: orderData.restaurant_id, // ✅ AGREGAR
      items: orderData.items,
      total: orderData.total,
      address: orderData.address,
      phone: orderData.phone,
      payment_method: orderData.payment_method,
      ...orderData,
    };

    return await api.post("/orders", requiredData);
  },

  // Roles (PREPARADO PARA EL FUTURO)
  async getUserRoles() {
    return await api.get("/user/roles");
  },

  async switchRole(roleData) {
    return await api.post("/user/switch-role", roleData);
  },

  // RESTAURANTES - Dashboard
  async getRestaurantDashboardStats(restaurantId) {
    return await api.get(`/restaurants/${restaurantId}/dashboard-stats`);
  },
};
