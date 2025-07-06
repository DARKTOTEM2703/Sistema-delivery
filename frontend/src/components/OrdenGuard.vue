<template>
  <Teleport to="body">
    <div v-if="!isAuthenticated" class="auth-required-modal">
      <div class="modal-overlay" @click="$emit('close')"></div>
      <div class="modal-content">
        <div class="modal-header">
          <h2>🔐 Cuenta Requerida</h2>
          <button @click="$emit('close')" class="close-btn">×</button>
        </div>
        
        <div class="modal-body">
          <div class="security-message">
            <div class="icon">🛡️</div>
            <h3>Por tu seguridad</h3>
            <p>Para evitar pedidos fantasma y garantizar un mejor servicio, es necesario tener una cuenta verificada para realizar pedidos.</p>
          </div>
          
          <div class="benefits">
            <h4>Beneficios de tener cuenta:</h4>
            <ul>
              <li>✅ Historial de pedidos</li>
              <li>✅ Direcciones guardadas</li>
              <li>✅ Pedidos más rápidos</li>
              <li>✅ Soporte personalizado</li>
              <li>✅ Ofertas exclusivas</li>
            </ul>
          </div>
        </div>
        
        <div class="modal-actions">
          <button @click="showRegister" class="btn-primary">
            🚀 Crear Cuenta Gratis
          </button>
          <button @click="showLogin" class="btn-secondary">
            🔑 Ya tengo cuenta
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, inject, onMounted, onUnmounted } from 'vue'

const auth = inject('auth')
const isAuthenticated = computed(() => auth.isAuthenticated())
const emit = defineEmits(['close'])

function showRegister() {
  auth.showLoginModal('register') // ✅ PASAR 'register' COMO PARÁMETRO
  emit('close')
}

function showLogin() {
  auth.showLoginModal('login') // ✅ PASAR 'login' COMO PARÁMETRO
  emit('close')
}

// Prevenir scroll del body cuando el modal está abierto
function preventBodyScroll() {
  document.body.style.overflow = 'hidden'
  document.body.style.paddingRight = '0px'
}

function restoreBodyScroll() {
  document.body.style.overflow = ''
  document.body.style.paddingRight = ''
}

// Manejar tecla ESC
function handleEscape(event) {
  if (event.key === 'Escape') {
    emit('close')
  }
}

onMounted(() => {
  if (!isAuthenticated.value) {
    preventBodyScroll()
    document.addEventListener('keydown', handleEscape)
  }
})

onUnmounted(() => {
  restoreBodyScroll()
  document.removeEventListener('keydown', handleEscape)
})
</script>

<style scoped>
/* ✅ MODAL EN PANTALLA COMPLETA */
.auth-required-modal {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  z-index: 999999 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  margin: 0 !important;
  padding: 1rem;
  box-sizing: border-box;
  backdrop-filter: blur(3px);
}

.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  z-index: 1;
}

.modal-content {
  position: relative;
  background: white;
  border-radius: 16px;
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 20px 25px -5px rgba(0, 0, 0, 0.1);
  z-index: 10;
  margin: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  background: white;
  border-radius: 16px 16px 0 0;
  position: sticky;
  top: 0;
  z-index: 10;
}

.modal-header h2 {
  margin: 0;
  color: #2d3748;
  font-size: 1.25rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #a0aec0;
  padding: 4px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  line-height: 1;
}

.close-btn:hover {
  background: #f7fafc;
  color: #4a5568;
  transform: scale(1.1);
}

.modal-body {
  padding: 1.5rem;
}

.security-message {
  text-align: center;
  padding: 2rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  margin-bottom: 1.5rem;
}

.security-message .icon {
  font-size: 3.5rem;
  margin-bottom: 1rem;
  display: block;
}

.security-message h3 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.security-message p {
  margin: 0;
  opacity: 0.95;
  line-height: 1.6;
  font-size: 1rem;
}

.benefits {
  margin-top: 1rem;
}

.benefits h4 {
  margin: 0 0 1rem 0;
  color: #4a5568;
  font-size: 1.1rem;
  font-weight: 600;
}

.benefits ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.benefits li {
  padding: 10px 0;
  border-bottom: 1px solid #eee;
  color: #4a5568;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.benefits li:last-child {
  border-bottom: none;
}

.modal-actions {
  display: flex;
  gap: 12px;
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 0 0 16px 16px;
}

.btn-primary {
  flex: 1;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 14px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1rem;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  flex: 1;
  background: transparent;
  color: #667eea;
  border: 2px solid #667eea;
  padding: 14px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1rem;
}

.btn-secondary:hover {
  background: #667eea;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

/* ✅ RESPONSIVE MEJORADO */
@media (max-width: 640px) {
  .auth-required-modal {
    padding: 0.5rem !important;
  }
  
  .modal-content {
    width: 95%;
    max-height: 95vh;
  }
  
  .modal-header,
  .modal-body,
  .modal-actions {
    padding: 1rem;
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .security-message {
    padding: 1.5rem 1rem;
  }
  
  .security-message .icon {
    font-size: 2.5rem;
  }
  
  .security-message h3 {
    font-size: 1.25rem;
  }
}

/* ✅ ANIMACIONES MEJORADAS */
@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes overlayFadeIn {
  from {
    opacity: 0;
    backdrop-filter: blur(0px);
  }
  to {
    opacity: 1;
    backdrop-filter: blur(3px);
  }
}

.auth-required-modal {
  animation: overlayFadeIn 0.3s ease-out;
}

.modal-content {
  animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* ✅ EVITAR BUGS VISUALES */
* {
  box-sizing: border-box;
}

/* ✅ ASEGURAR QUE EL MODAL ESTÉ ENCIMA DE TODO */
.auth-required-modal {
  isolation: isolate;
}
</style>