<template>
  <div class="login-container" v-if="auth.state.showLoginModal">
    <div class="login-card">
      <div class="login-header">
        <h2>{{ isRegister ? 'Crear cuenta' : 'Iniciar sesión' }}</h2>
        <button @click="auth.closeLoginModal()" class="close-btn">×</button>
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
              :disabled="loading"
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
              :disabled="loading"
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
              :disabled="loading"
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
              :disabled="loading"
            >
          </div>
          
          <div v-if="error" class="error-message">
            {{ error }}
          </div>
          
          <div v-if="success" class="success-message">
            {{ success }}
          </div>
          
          <button type="submit" class="submit-btn" :disabled="loading">
            <span v-if="loading" class="loader"></span>
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
import { ref, inject, watch } from 'vue'; // ✅ AGREGAR watch

const auth = inject('auth');
const isRegister = ref(false);
const loading = ref(false);
const error = ref('');
const success = ref('');

// ✅ AGREGAR ESTE WATCHER
watch(() => auth.state.loginMode, (newMode) => {
  isRegister.value = newMode === 'register';
}, { immediate: true });

const formData = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

function toggleForm() {
  isRegister.value = !isRegister.value;
  error.value = '';
  success.value = '';
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
  success.value = '';
  
  try {
    if (isRegister.value) {
      console.log('📝 Iniciando registro...');
      await auth.register(formData.value);
      success.value = '¡Registro exitoso! Bienvenido/a.';
    } else {
      console.log('🔑 Iniciando login...');
      await auth.login({
        email: formData.value.email,        // ✅ AGREGAR EMAIL
        password: formData.value.password
      });
      success.value = '¡Login exitoso! Bienvenido/a de vuelta.';
    }
    
    // Cerrar modal después de un breve delay para mostrar el mensaje
    setTimeout(() => {
      auth.closeLoginModal();
      // Reset form
      formData.value = {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      };
      success.value = '';
    }, 1500);
    
  } catch (err) {
    console.error('❌ Error en autenticación:', err);
    
    // Manejar diferentes tipos de errores
    if (err.response?.status === 422) {
      // Errores de validación
      const errors = err.response.data.errors;
      if (errors) {
        const firstError = Object.values(errors)[0];
        error.value = Array.isArray(firstError) ? firstError[0] : firstError;
      } else {
        error.value = err.response.data.message || 'Error de validación';
      }
    } else if (err.response?.status === 401) {
      error.value = 'Credenciales incorrectas';
    } else if (err.response?.status === 0 || !err.response) {
      error.value = 'Error de conexión. Verifica que el servidor esté funcionando.';
    } else {
      error.value = err.response?.data?.message || 'Error en la autenticación';
    }
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
  
  // Validación básica de email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(formData.value.email)) {
    error.value = 'Ingresa un email válido';
    return false;
  }
  
  if (!formData.value.password) {
    error.value = 'La contraseña es requerida';
    return false;
  }
  
  if (formData.value.password.length < 8) {
    error.value = 'La contraseña debe tener al menos 8 caracteres';
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
  z-index: 1000;
  backdrop-filter: blur(2px);
}

.login-card {
  background-color: var(--card-bg);
  border-radius: 12px;
  box-shadow: var(--box-shadow);
  width: 90%;
  max-width: 400px;
  overflow: hidden;
}

.login-header {
  background: linear-gradient(135deg, #ff7b00, #ff5722);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.login-header h2 {
  margin: 0;
  font-size: 1.5rem;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s;
}

.close-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.login-body {
  padding: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-color);
  font-weight: 500;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-bg);
  color: var(--text-color);
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #ff7b00;
  box-shadow: 0 0 0 3px rgba(255, 123, 0, 0.1);
}

.form-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  background: #fee;
  color: #c53030;
  padding: 0.75rem;
  border-radius: 6px;
  border-left: 4px solid #e53e3e;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.success-message {
  background: #f0fff4;
  color: #2d5016;
  padding: 0.75rem;
  border-radius: 6px;
  border-left: 4px solid #38a169;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.submit-btn {
  width: 100%;
  background: linear-gradient(135deg, #ff7b00, #ff5722);
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  position: relative;
  min-height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(255, 123, 0, 0.3);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.loader {
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  margin-right: 0.5rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.toggle-form {
  text-align: center;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.toggle-form p {
  margin: 0;
  color: var(--text-color);
}

.toggle-form a {
  color: #ff7b00;
  text-decoration: none;
  font-weight: 500;
}

.toggle-form a:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .login-card {
    width: 95%;
    margin: 1rem;
  }
  
  .login-body {
    padding: 1.5rem;
  }
}
</style>