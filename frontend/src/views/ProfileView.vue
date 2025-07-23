<template>
  <div class="profile-container">
    <h1>Mi Perfil</h1>
    
    <div class="profile-content">
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-avatar">
            {{ getInitials(user.name) }}
          </div>
          <div class="profile-info">
            <h2>{{ user.name }}</h2>
            <p>{{ user.email }}</p>
          </div>
        </div>
        
        <div class="profile-actions">
          <button class="edit-btn" @click="editMode = !editMode">
            {{ editMode ? 'Cancelar' : 'Editar perfil' }}
          </button>
        </div>
        
        <div v-if="editMode" class="profile-edit-form">
          <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" id="name" v-model="formData.name">
          </div>
          
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" v-model="formData.email">
          </div>
          
          <div class="form-group">
            <label for="password">Nueva contraseña</label>
            <input type="password" id="password" v-model="formData.password">
          </div>
          
          <div class="form-group">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" v-model="formData.password_confirmation">
          </div>
          
          <button class="save-btn" @click="updateProfile">Guardar cambios</button>
        </div>
      </div>
      
      <div class="addresses-card">
        <h2>Mis direcciones</h2>
        
        <div v-if="addresses.length > 0" class="addresses-list">
          <div v-for="address in addresses" :key="address.id" class="address-item">
            <div class="address-icon">🏠</div>
            <div class="address-details">
              <h3>{{ address.name }}</h3>
              <p>{{ address.street }}</p>
              <p>{{ address.city }}, {{ address.zip }}</p>
            </div>
            <div class="address-actions">
              <button class="edit-address-btn">Editar</button>
              <button class="delete-address-btn">Eliminar</button>
            </div>
          </div>
        </div>
        
        <div v-else class="no-addresses">
          <p>No tienes direcciones guardadas</p>
        </div>
        
        <button class="add-address-btn" @click="showAddressForm = true">
          Añadir dirección
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject, onMounted } from 'vue';
import axios from 'axios';

const auth = inject('auth');
const user = ref(auth.getUser());
const editMode = ref(false);
const showAddressForm = ref(false);
const addresses = ref([]);

const formData = ref({
  name: user.value.name,
  email: user.value.email,
  password: '',
  password_confirmation: ''
});

onMounted(async () => {
  try {
    const response = await axios.get('/api/addresses');
    addresses.value = response.data;
  } catch (error) {
    console.error('Error al cargar direcciones:', error);
  }
});

function getInitials(name) {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
}

async function updateProfile() {
  try {
    // Solo enviar la contraseña si se ha establecido
    const data = {
      name: formData.value.name,
      email: formData.value.email
    };
    
    if (formData.value.password) {
      data.password = formData.value.password;
      data.password_confirmation = formData.value.password_confirmation;
    }
    
    const response = await axios.put('/api/profile', data);
    user.value = response.data;
    auth.setUser(response.data);
    editMode.value = false;
    
    // Mostrar un mensaje de éxito
    // toast.success('Perfil actualizado correctamente');
  } catch (error) {
    console.error('Error al actualizar perfil:', error);
    // toast.error('Error al actualizar perfil');
  }
}
</script>

<style scoped>
.profile-container {
  max-width: 800px;
  margin: 2rem auto;
  padding: 0 1rem;
}

h1 {
  color: var(--text-color);
  margin-bottom: 2rem;
}

.profile-content {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
}

@media (min-width: 768px) {
  .profile-content {
    grid-template-columns: 1fr 1fr;
  }
}

.profile-card, .addresses-card {
  background-color: var(--card-bg);
  border-radius: 8px;
  box-shadow: var(--box-shadow);
  padding: 1.5rem;
  border: 1px solid var(--border-color);
}

.profile-header {
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;
}

.profile-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: var(--button-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: bold;
  margin-right: 1rem;
}

.profile-info h2 {
  margin: 0;
  color: var(--text-color);
}

.profile-info p {
  margin: 0.25rem 0 0;
  color: var(--text-color);
  opacity: 0.7;
}

.profile-actions {
  margin-bottom: 1.5rem;
}

.edit-btn, .save-btn, .add-address-btn {
  padding: 0.5rem 1rem;
  background: var(--button-primary);
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background-color: var(--card-bg);
  color: var(--text-color);
}

.addresses-card h2 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  color: var(--text-color);
}

.addresses-list {
  margin-bottom: 1.5rem;
}

.address-item {
  display: flex;
  align-items: flex-start;
  padding: 1rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  margin-bottom: 1rem;
}

.address-icon {
  margin-right: 1rem;
  font-size: 1.5rem;
}

.address-details {
  flex: 1;
}

.address-details h3 {
  margin: 0;
  color: var(--text-color);
}

.address-details p {
  margin: 0.25rem 0;
  color: var(--text-color);
  opacity: 0.7;
}

.address-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.edit-address-btn, .delete-address-btn {
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.85rem;
}

.edit-address-btn {
  background-color: rgba(0, 123, 255, 0.1);
  color: #007bff;
}

.delete-address-btn {
  background-color: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

.no-addresses {
  text-align: center;
  padding: 2rem 0;
  color: var(--text-color);
  opacity: 0.7;
}

.add-address-btn {
  width: 100%;
}

@media (max-width: 640px) {
  .profile-container {
    margin: 1.5rem auto;
  }
  
  .profile-header {
    flex-direction: column;
    align-items: flex-start;
    text-align: center;
    gap: 1rem;
  }
  
  .profile-avatar {
    margin: 0 auto;
  }
  
  .profile-info {
    width: 100%;
    text-align: center;
  }
  
  .address-item {
    flex-direction: column;
    gap: 1rem;
  }
  
  .address-icon {
    margin: 0 auto;
  }
  
  .address-details {
    text-align: center;
  }
  
  .address-actions {
    width: 100%;
    flex-direction: row;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .profile-container {
    margin: 1rem auto;
    padding: 0 0.75rem;
  }
  
  h1 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .profile-card, .addresses-card {
    padding: 1rem;
  }
  
  .form-group input {
    padding: 0.6rem;
  }
}
</style>