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
  // Productos
  async getProducts() {
    return await api.get("/products");
  },

  async getProduct(id) {
    return await api.get(`/products/${id}`);
  },

  // Autenticación
  async login(credentials) {
    return await api.post("/login", credentials);
  },

  async register(userData) {
    return await api.post("/register", userData);
  },

  async logout() {
    return await api.post("/logout");
  },

  // Pedidos
  async createOrder(orderData) {
    return await api.post("/orders", orderData);
  },
};
