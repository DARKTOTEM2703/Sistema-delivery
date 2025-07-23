<template>
  <div class="create-restaurant-container">
    <!-- Progress indicator -->
    <div class="verification-progress">
      <div class="step" :class="{ active: currentStep >= 1, completed: currentStep > 1 }">
        <span class="step-number">1</span>
        <span class="step-label">Información Básica</span>
      </div>
      <div class="step" :class="{ active: currentStep >= 2, completed: currentStep > 2 }">
        <span class="step-number">2</span>
        <span class="step-label">Documentos</span>
      </div>
      <div class="step" :class="{ active: currentStep >= 3, completed: currentStep > 3 }">
        <span class="step-number">3</span>
        <span class="step-label">Verificación</span>
      </div>
    </div>

    <!-- Paso 1: Información Básica -->
    <div v-if="currentStep === 1" class="step-content">
      <div class="form-header">
        <h1>🏪 Registra tu Restaurante</h1>
        <p>Únete a nuestra plataforma y vende tus deliciosos platillos</p>
      </div>

      <form @submit.prevent="createRestaurant" class="restaurant-form">
        <!-- Información básica -->
        <div class="form-section">
          <h2>📋 Información Básica</h2>
          
          <div class="form-group">
            <label>Nombre del restaurante *</label>
            <input 
              v-model="form.name" 
              type="text" 
              required 
              placeholder="Ej: Pizzería Don Luigi"
            >
          </div>

          <div class="form-group">
            <label>Descripción *</label>
            <textarea 
              v-model="form.description" 
              required 
              placeholder="Describe tu restaurante y especialidades..."
              rows="3"
            ></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Categoría *</label>
              <select v-model="form.category" required>
                <option value="">Selecciona una categoría</option>
                <option value="italiana">🍕 Italiana</option>
                <option value="mexicana">🌮 Mexicana</option>
                <option value="china">🥡 China</option>
                <option value="japonesa">🍱 Japonesa</option>
                <option value="hamburguesas">🍔 Hamburguesas</option>
                <option value="pizzas">🍕 Pizzas</option>
                <option value="ensaladas">🥗 Ensaladas</option>
                <option value="postres">🍰 Postres</option>
                <option value="comida_rapida">⚡ Comida Rápida</option>
              </select>
            </div>

            <div class="form-group">
              <label>Teléfono *</label>
              <input 
                v-model="form.phone" 
                type="tel" 
                required 
                placeholder="+52 55 1234 5678"
              >
            </div>
          </div>

          <div class="form-group">
            <label>Email de contacto *</label>
            <input 
              v-model="form.email" 
              type="email" 
              required 
              placeholder="contacto@turestaurante.com"
            >
          </div>
        </div>

        <!-- Ubicación -->
        <div class="form-section">
          <h2>📍 Ubicación y Entrega</h2>
          
          <div class="form-group">
            <label>Dirección completa *</label>
            <input 
              v-model="form.address" 
              type="text" 
              required 
              placeholder="Calle, número, colonia, ciudad"
            >
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Costo de envío</label>
              <input 
                v-model.number="form.delivery_fee" 
                type="number" 
                step="0.01" 
                placeholder="0.00"
              >
            </div>

            <div class="form-group">
              <label>Pedido mínimo</label>
              <input 
                v-model.number="form.minimum_order" 
                type="number" 
                step="0.01" 
                placeholder="100.00"
              >
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Tiempo de entrega (min)</label>
              <input 
                v-model.number="form.delivery_time_min" 
                type="number" 
                placeholder="30"
              >
            </div>

            <div class="form-group">
              <label>Tiempo de entrega (max)</label>
              <input 
                v-model.number="form.delivery_time_max" 
                type="number" 
                placeholder="45"
              >
            </div>
          </div>
        </div>

        <!-- Horarios -->
        <div class="form-section">
          <h2>🕐 Horarios de Operación</h2>
          
          <div class="schedule-grid">
            <div v-for="day in daysOfWeek" :key="day.key" class="day-schedule">
              <div class="day-header">
                <label class="day-label">{{ day.label }}</label>
                <input 
                  v-model="form.business_hours[day.key].is_open" 
                  type="checkbox"
                  class="day-toggle"
                >
              </div>
              
              <div v-if="form.business_hours[day.key].is_open" class="time-inputs">
                <input 
                  v-model="form.business_hours[day.key].open" 
                  type="time"
                  class="time-input"
                >
                <span>a</span>
                <input 
                  v-model="form.business_hours[day.key].close" 
                  type="time"
                  class="time-input"
                >
              </div>
            </div>
          </div>
        </div>

        <!-- Botones -->
        <div class="form-actions">
          <button type="button" @click="$router.go(-1)" class="btn-secondary">
            Cancelar
          </button>
          <button type="submit" :disabled="loading" class="btn-primary">
            {{ loading ? 'Creando...' : 'Crear Restaurante' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Paso 2: Documentos -->
    <div v-if="currentStep === 2" class="step-content">
      <h2>📋 Documentos Requeridos</h2>
      
      <div class="document-upload">
        <div class="document-item">
          <h3>🆔 Identificación Oficial</h3>
          <input type="file" @change="handleFileUpload('id', $event)" accept="image/*,.pdf">
          <p class="document-note">INE, Pasaporte o Cédula Profesional</p>
        </div>

        <div class="document-item">
          <h3>🏢 Comprobante de Domicilio del Negocio</h3>
          <input type="file" @change="handleFileUpload('address_proof', $event)" accept="image/*,.pdf">
          <p class="document-note">Recibo de luz, agua o predial (máximo 3 meses)</p>
        </div>

        <div class="document-item">
          <h3>📄 Permiso Sanitario</h3>
          <input type="file" @change="handleFileUpload('health_permit', $event)" accept="image/*,.pdf">
          <p class="document-note">Permiso de COFEPRIS o autoridad local</p>
        </div>

        <div class="document-item">
          <h3>📸 Fotos del Establecimiento</h3>
          <input type="file" @change="handleFileUpload('establishment_photos', $event)" multiple accept="image/*">
          <p class="document-note">Mínimo 3 fotos: fachada, cocina, área de preparación</p>
        </div>
      </div>
    </div>

    <!-- Paso 3: Verificación Pendiente -->
    <div v-if="currentStep === 3" class="step-content">
      <div class="verification-pending">
        <div class="pending-icon">⏳</div>
        <h2>Verificación en Proceso</h2>
        <p>Hemos recibido tu solicitud. Nuestro equipo revisará tus documentos en 24-48 horas.</p>
        
        <div class="next-steps">
          <h3>📋 Próximos pasos:</h3>
          <ul>
            <li>✅ Verificación de documentos</li>
            <li>📞 Llamada de confirmación</li>
            <li>🏪 Activación del restaurante</li>
          </ul>
        </div>

        <div class="contact-info">
          <p>¿Tienes dudas? Contáctanos: 📞 01-800-DELIVERY</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, inject } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const auth = inject('auth')
const loading = ref(false)

const currentStep = ref(1)
const documents = reactive({
  id: null,
  address_proof: null,
  health_permit: null,
  establishment_photos: []
})

const daysOfWeek = [
  { key: 'monday', label: 'Lunes' },
  { key: 'tuesday', label: 'Martes' },
  { key: 'wednesday', label: 'Miércoles' },
  { key: 'thursday', label: 'Jueves' },
  { key: 'friday', label: 'Viernes' },
  { key: 'saturday', label: 'Sábado' },
  { key: 'sunday', label: 'Domingo' }
]

const form = reactive({
  name: '',
  description: '',
  category: '',
  address: '',
  phone: '',
  email: '',
  delivery_fee: 0,
  minimum_order: 0,
  delivery_time_min: 30,
  delivery_time_max: 45,
  business_hours: {
    monday: { is_open: true, open: '09:00', close: '22:00' },
    tuesday: { is_open: true, open: '09:00', close: '22:00' },
    wednesday: { is_open: true, open: '09:00', close: '22:00' },
    thursday: { is_open: true, open: '09:00', close: '22:00' },
    friday: { is_open: true, open: '09:00', close: '23:00' },
    saturday: { is_open: true, open: '10:00', close: '23:00' },
    sunday: { is_open: false, open: '', close: '' }
  },
  delivery_zones: [{ name: 'Zona 1', radius: 5 }]
})

const createRestaurant = async () => {
  try {
    loading.value = true
    
    const response = await api.post('/restaurants', form)
    
    // ACTUALIZAR USUARIO MANUALMENTE
    const currentUser = auth.getUser()
    if (currentUser) {
      const updatedUser = { 
        ...currentUser, 
        role: 'owner',
        owned_restaurant_id: response.data.id 
      }
      auth.setUser(updatedUser)
    }
    
    alert('¡Restaurante creado exitosamente! 🎉')
    
    // USAR LA ID CORRECTA DE LA RESPUESTA
    router.push(`/owner/dashboard/${response.data.id}`)
    
  } catch (error) {
    console.error('Error creando restaurante:', error)
    alert('Error al crear el restaurante. Intenta de nuevo.')
  } finally {
    loading.value = false
  }
}

const handleFileUpload = (type, event) => {
  const files = event.target.files
  
  if (type === 'establishment_photos') {
    documents[type] = Array.from(files)
  } else {
    documents[type] = files[0]
  }
}

const submitForVerification = async () => {
  try {
    const formData = new FormData()
    
    // Agregar datos del restaurante
    Object.keys(form).forEach(key => {
      if (typeof form[key] === 'object') {
        formData.append(key, JSON.stringify(form[key]))
      } else {
        formData.append(key, form[key])
      }
    })
    
    // Agregar documentos
    Object.keys(documents).forEach(key => {
      if (documents[key]) {
        if (Array.isArray(documents[key])) {
          documents[key].forEach((file, index) => {
            formData.append(`${key}[${index}]`, file)
          })
        } else {
          formData.append(key, documents[key])
        }
      }
    })
    
    // Marcar como pendiente de verificación
    formData.append('verification_status', 'pending')
    formData.append('is_active', false)
    
    const response = await api.post('/restaurants/submit-for-verification', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    currentStep.value = 3
    
  } catch (error) {
    console.error('Error enviando para verificación:', error)
    alert('Error al enviar la solicitud. Intenta de nuevo.')
  }
}
</script>

<style scoped>
.create-restaurant-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.verification-progress {
  display: flex;
  justify-content: space-between;
  margin-bottom: 2rem;
}

.step {
  flex: 1;
  position: relative;
  padding: 1rem;
  text-align: center;
}

.step-number {
  display: block;
  width: 2rem;
  height: 2rem;
  line-height: 2rem;
  border-radius: 50%;
  background: var(--primary-color);
  color: white;
  font-weight: 600;
  margin: 0 auto 0.5rem;
}

.step-label {
  font-size: 0.875rem;
  color: var(--text-color);
}

.step.completed .step-number {
  background: var(--success-color);
}

.step.completed .step-label {
  color: var(--success-color);
}

.step.active .step-number {
  background: var(--warning-color);
}

.step-content {
  background: var(--card-bg);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.form-header {
  text-align: center;
  margin-bottom: 2rem;
}

.form-header h1 {
  color: var(--primary-color);
  margin-bottom: 0.5rem;
}

.form-section {
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid var(--border-color);
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.form-section h2 {
  color: var(--text-color);
  margin-bottom: 1rem;
  font-size: 1.25rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: var(--text-color);
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-bg);
  color: var(--text-color);
  font-size: 1rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.schedule-grid {
  display: grid;
  gap: 1rem;
}

.day-schedule {
  padding: 1rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-bg);
}

.day-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.time-inputs {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.time-input {
  flex: 1;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn-primary,
.btn-secondary {
  padding: 0.75rem 2rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  border: none;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-secondary {
  background: var(--secondary-color);
  color: var(--text-color);
}

.document-upload {
  display: grid;
  gap: 1.5rem;
}

.document-item {
  padding: 1.5rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-bg);
}

.document-item h3 {
  margin-bottom: 0.5rem;
  font-size: 1.125rem;
  color: var(--text-color);
}

.document-note {
  font-size: 0.875rem;
  color: var(--text-color);
}

.verification-pending {
  text-align: center;
  padding: 2rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-bg);
}

.pending-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.next-steps {
  text-align: left;
  margin: 1rem 0;
}

.contact-info {
  margin-top: 1rem;
  font-size: 0.875rem;
  color: var(--text-color);
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
}

@media (max-width: 640px) {
  .create-restaurant-container {
    padding: 1.5rem 0.75rem;
  }
  
  .verification-progress {
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
  }
  
  .step {
    width: 100%;
    max-width: 180px;
  }
  
  .step-content {
    padding: 1.5rem 1rem;
  }
  
  .form-header h1 {
    font-size: 1.5rem;
  }
  
  .form-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
  }
  
  .form-section h2 {
    font-size: 1.2rem;
  }
  
  .document-item {
    padding: 1.25rem;
  }
}

@media (max-width: 480px) {
  .step {
    padding: 0.75rem;
  }
  
  .step-content {
    padding: 1.25rem 0.75rem;
  }
  
  .form-header h1 {
    font-size: 1.3rem;
  }
  
  .form-group label {
    font-size: 0.9rem;
  }
  
  .form-group input, 
  .form-group select, 
  .form-group textarea {
    padding: 0.6rem;
  }
  
  .btn-primary, 
  .btn-secondary {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }
  
  .document-item {
    padding: 1rem;
  }
}
</style>