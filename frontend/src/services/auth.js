import { reactive } from "vue";

const state = reactive({
  user: JSON.parse(localStorage.getItem("user")) || null,
  isAuthenticated: !!localStorage.getItem("token"),
  showLoginModal: false,
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
  },

  setAuthenticated(value) {
    state.isAuthenticated = value;
  },

  showLoginModal() {
    state.showLoginModal = true;
  },

  closeLoginModal() {
    state.showLoginModal = false;
  },

  async logout() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    state.user = null;
    state.isAuthenticated = false;
  },
};
