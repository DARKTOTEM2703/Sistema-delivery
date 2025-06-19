<template>
  <div class="hero">
    <div class="hero-content">
      <h1 class="hero-title">Deliciosa comida a domicilio</h1>
      <p class="hero-subtitle">Ordena ahora y recibe tu comida en 30 minutos o menos</p>
    </div>
  </div>
</template>

<script setup>
// No se requiere lógica adicional para este componente
</script>

<style scoped>
.hero {
  width: 100%;
  background: linear-gradient(to right, #ff7b00, #ff0000);
  padding: 4rem 1rem;
  text-align: center;
  display: flex;
  justify-content: center;
  align-items: center;
}

.hero-content {
  max-width: 800px;
  margin: 0 auto;
}

.hero-title {
  color: white;
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
}

.hero-subtitle {
  color: white;
  font-size: 1.25rem;
  margin: 0;
}

@media (max-width: 768px) {
  .hero {
    padding: 3rem 1rem;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-subtitle {
    font-size: 1.1rem;
  }
}
</style>