<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h2>{{ isRegister ? 'Crear cuenta' : 'Iniciar sesión' }}</h2>
      </div>
      
      <div class="login-body">
        <form @submit.prevent="handleSubmit">
          <!-- Campo de nombre (solo para registro) -->
          <div class="form-group" v-if="isRegister">
            <label for="name">Nombre completo</label>
            <input 
              type="text" 
              id="name" 
              v-model="formData.name" 
              required 
              class="form-input"
            >
          </div>
          
          <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input 
              type="email" 
              id="email" 
              v-model="formData.email" 
              required 
              class="form-input"
            >
          </div>
          
          <div class="form-group">
            <label for="password">Contraseña</label>
            <input 
              type="password" 
              id="password" 
              v-model="formData.password" 
              required 
              class="form-input"
            >
          </div>
          
          <!-- Confirmar contraseña (solo para registro) -->
          <div class="form-group" v-if="isRegister">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input 
              type="password" 
              id="password_confirmation" 
              v-model="formData.password_confirmation" 
              required 
              class="form-input"
            >
          </div>
          
          <div v-if="error" class="error-message">
            {{ error }}
          </div>
          
          <button type="submit" class="submit-btn" :disabled="loading">
            {{ loading ? 'Procesando...' : (isRegister ? 'Registrarme' : 'Iniciar sesión') }}
          </button>
        </form>
        
        <div class="toggle-form">
          <p>
            {{ isRegister ? '¿Ya tienes cuenta?' : '¿No tienes cuenta?' }}
            <a href="#" @click.prevent="toggleForm">
              {{ isRegister ? 'Inicia sesión' : 'Regístrate' }}
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue';

const auth = inject('auth');
const isRegister = ref(false);
const loading = ref(false);
const error = ref('');

const formData = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

function toggleForm() {
  isRegister.value = !isRegister.value;
  error.value = '';
  formData.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  };
}

async function handleSubmit() {
  if (!validateForm()) {
    return;
  }
  
  loading.value = true;
  error.value = '';
  
  try {
    if (isRegister.value) {
      await auth.register(formData.value);
    } else {
      await auth.login({
        email: formData.value.email,
        password: formData.value.password
      });
    }
    
    // Cerrar modal al completar
    auth.closeLoginModal();
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Error en la autenticación';
  } finally {
    loading.value = false;
  }
}

function validateForm() {
  if (isRegister.value && !formData.value.name.trim()) {
    error.value = 'El nombre es requerido';
    return false;
  }
  
  if (!formData.value.email.trim()) {
    error.value = 'El email es requerido';
    return false;
  }
  
  if (!formData.value.password) {
    error.value = 'La contraseña es requerida';
    return false;
  }
  
  if (isRegister.value && formData.value.password !== formData.value.password_confirmation) {
    error.value = 'Las contraseñas no coinciden';
    return false;
  }
  
  return true;
}
</script>

<style scoped>
.login-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1001;
  backdrop-filter: blur(3px);
}

.login-card {
  background-color: var(--card-bg);
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
  box-shadow: var(--box-shadow);
  border: 1px solid var(--border-color);
}

.login-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.login-header h2 {
  margin: 0;
  color: var(--text-color);
  text-align: center;
}

.login-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background-color: var(--card-bg);
  color: var(--text-color);
}

.error-message {
  color: #ff4d4f;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.submit-btn {
  width: 100%;
  padding: 0.75rem;
  background: var(--button-primary);
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.toggle-form {
  margin-top: 1.5rem;
  text-align: center;
}

.toggle-form a {
  color: #ff7b00;
  text-decoration: none;
  font-weight: 500;
}

.toggle-form a:hover {
  text-decoration: underline;
}
</style>