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
  async get(url, config = {}) {
    return await api.get(url, config);
  },

  async post(url, data = {}, config = {}) {
    return await api.post(url, data, config);
  },

  async put(url, data = {}, config = {}) {
    return await api.put(url, data, config);
  },

  async patch(url, data = {}, config = {}) {
    return await api.patch(url, data, config);
  },

  async delete(url, config = {}) {
    return await api.delete(url, config);
  },

  // Productos
  async getProducts() {
    return await this.get("/products");
  },

  async getProduct(id) {
    return await this.get(`/products/${id}`);
  },

  async createProduct(productData) {
    return await this.post("/products", productData);
  },

  async updateProduct(id, productData) {
    return await this.put(`/products/${id}`, productData);
  },

  async deleteProduct(id) {
    return await this.delete(`/products/${id}`);
  },

  // RESTAURANTES
  async getRestaurants() {
    try {
      return await this.get("/restaurants");
    } catch (error) {
      console.error("Error obteniendo restaurantes:", error);
      // Si hay un error, devolver un array vacío en lugar del fallback
      return { data: [] };
    }
  },

  async getRestaurant(id) {
    try {
      return await this.get(`/restaurants/${id}`);
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
      return await this.get(`/restaurants/${restaurantId}/products`);
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
      return await this.get("/restaurants/categories");
    } catch (error) {
      return {
        data: ["italiana", "americana", "japonesa", "mexicana", "saludable"],
      };
    }
  },

  // Autenticación
  async login(credentials) {
    try {
      console.log("🔑 Intentando login:", credentials);
      const response = await this.post("/login", credentials);
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
      const response = await this.post("/register", userData);
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
      return await this.post("/logout");
    } catch (error) {
      console.error("❌ Error en logout:", error);
      throw error;
    }
  },

  // Pedidos
  async createOrder(orderData) {
    const requiredData = {
      restaurant_id: orderData.restaurant_id,
      items: orderData.items,
      total: orderData.total,
      address: orderData.address,
      phone: orderData.phone,
      payment_method: orderData.payment_method,
      ...orderData,
    };

    return await this.post("/orders", requiredData);
  },

  // Dashboard y estadísticas
  async getRestaurantDashboardStats(restaurantId) {
    try {
      return await this.get(`/restaurants/${restaurantId}/dashboard-stats`);
    } catch (error) {
      // Fallback con datos de ejemplo
      return {
        data: {
          todayOrders: 12,
          todayRevenue: 450.75,
          monthlyOrders: 340,
          monthlyRevenue: 12500.5,
        },
      };
    }
  },

  // POS endpoints
  async openCashShift(registerId, data) {
    return await this.post(`/cash-registers/${registerId}/open-shift`, data);
  },

  async closeCashShift(shiftId, data) {
    return await this.post(`/cash-shifts/${shiftId}/close`, data);
  },

  async getCashRegisters() {
    try {
      return await this.get("/cash-registers");
    } catch (error) {
      return {
        data: [
          {
            id: 1,
            name: "Caja Principal",
            currentShift: null,
          },
        ],
      };
    }
  },

  async getSalesReport(restaurantId, params) {
    try {
      return await this.get(`/restaurants/${restaurantId}/sales-report`, {
        params,
      });
    } catch (error) {
      // Fallback con datos de ejemplo
      return {
        data: {
          totalSales: 15420.5,
          totalOrders: 47,
          averageOrder: 328.0,
          orders: [
            {
              id: 1,
              created_at: "2025-01-06T10:30:00",
              customer_name: "Juan Pérez",
              total: 250.5,
              status: "completado",
            },
            {
              id: 2,
              created_at: "2025-01-06T11:15:00",
              customer_name: "María García",
              total: 180.75,
              status: "entregado",
            },
          ],
        },
      };
    }
  },
};
