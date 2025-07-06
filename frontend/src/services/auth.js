import { reactive } from "vue";
import api from "./api";

const state = reactive({
  user: JSON.parse(localStorage.getItem("user")) || null,
  isAuthenticated: !!localStorage.getItem("token"),
  showLoginModal: false,
  loginMode: "login", // ✅ AGREGAR ESTA LÍNEA
});

export default {
  state,

  isAuthenticated() {
    return state.isAuthenticated;
  },

  getUser() {
    return state.user;
  },

  setUser(user) {
    state.user = user;
    localStorage.setItem("user", JSON.stringify(user));
  },

  setAuthenticated(value) {
    state.isAuthenticated = value;
  },

  showLoginModal(mode = "login") {
    // ✅ AGREGAR PARÁMETRO
    state.showLoginModal = true;
    state.loginMode = mode; // ✅ AGREGAR ESTA LÍNEA
  },

  closeLoginModal() {
    state.showLoginModal = false;
    state.loginMode = "login"; // ✅ RESETEAR MODO
  },

  // MÉTODO DE LOGIN COMPLETAMENTE IMPLEMENTADO
  async login(credentials) {
    try {
      console.log("🔑 Iniciando proceso de login...");

      const response = await api.login(credentials);
      const { user, token } = response.data;

      // Guardar token y usuario en localStorage
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));

      // Actualizar estado reactivo
      state.user = user;
      state.isAuthenticated = true;
      state.showLoginModal = false;

      console.log("✅ Login completado exitosamente");
      return response;
    } catch (error) {
      console.error("❌ Error en login:", error);

      // Limpiar estado en caso de error
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      state.user = null;
      state.isAuthenticated = false;

      throw error;
    }
  },

  // MÉTODO DE REGISTRO COMPLETAMENTE IMPLEMENTADO
  async register(userData) {
    try {
      console.log("📝 Iniciando proceso de registro...");

      const response = await api.register(userData);
      const { user, token } = response.data;

      // Guardar token y usuario en localStorage
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));

      // Actualizar estado reactivo
      state.user = user;
      state.isAuthenticated = true;
      state.showLoginModal = false;

      console.log("✅ Registro completado exitosamente");
      return response;
    } catch (error) {
      console.error("❌ Error en registro:", error);

      // Limpiar estado en caso de error
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      state.user = null;
      state.isAuthenticated = false;

      throw error;
    }
  },

  async logout() {
    try {
      // Intentar logout en el servidor
      await api.logout();
    } catch (error) {
      console.error("❌ Error al hacer logout en servidor:", error);
      // Continuar con logout local
    }

    // Limpiar estado local siempre
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    state.user = null;
    state.isAuthenticated = false;

    console.log("✅ Logout completado");
  },

  // Método para verificar si el token es válido
  async checkTokenValidity() {
    if (!state.isAuthenticated || !localStorage.getItem("token")) {
      return false;
    }

    try {
      const response = await api.get("/user");
      return true;
    } catch (error) {
      // Token inválido, limpiar estado
      await this.logout();
      return false;
    }
  },
};
