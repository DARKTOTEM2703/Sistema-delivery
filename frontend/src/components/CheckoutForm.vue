<template>
  <div class="checkout-container" v-if="isCheckoutActive">
    <div class="checkout-form">
      <h2>Finalizar Pedido</h2>
      
      <div class="form-group">
        <label for="name">Nombre completo</label>
        <input type="text" id="name" v-model="formData.name" required>
      </div>
      
      <div class="form-group">
        <label for="address">Dirección de entrega</label>
        <input type="text" id="address" v-model="formData.address" required>
      </div>
      
      <div class="form-group">
        <label for="phone">Teléfono</label>
        <input type="tel" id="phone" v-model="formData.phone" required>
      </div>
      
      <div class="form-group">
        <label for="paymentMethod">Método de pago</label>
        <select id="paymentMethod" v-model="formData.paymentMethod" required>
          <option value="efectivo">Efectivo</option>
          <option value="tarjeta">Tarjeta al entregar</option>
          <option value="online">Pago online</option>
        </select>
      </div>
      
      <div class="actions">
        <button class="cancel-btn" @click="$emit('cancel')">Cancelar</button>
        <button class="submit-btn" @click="submitOrder">Confirmar pedido</button>
      </div>
    </div>
  </div>
</template>