<template>
  <div class="hero">
    <div class="hero-content">
      <h1 class="hero-title">Deliciosa comida a domicilio</h1>
      <p class="hero-subtitle">Encuentra los mejores restaurantes cerca de ti</p>
    </div>
  </div>
</template>

<script setup>
// No se requiere lógica adicional para este componente
</script>

<style scoped>
.hero {
  background: linear-gradient(to right, #ff7b00, #ff0000);
  color: white;
  padding: 3rem 1rem;
  text-align: center;
  border-radius: 0 0 20px 20px;
  margin-bottom: 1.5rem;
}

.hero-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.hero-subtitle {
  font-size: 1.2rem;
  max-width: 600px;
  margin: 0 auto;
}

@media (max-width: 768px) {
  .hero {
    padding: 2.5rem 1rem;
    border-radius: 0 0 15px 15px;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-subtitle {
    font-size: 1.1rem;
  }
}

@media (max-width: 480px) {
  .hero {
    padding: 2rem 1rem;
    border-radius: 0 0 10px 10px;
  }
  
  .hero-title {
    font-size: 1.7rem;
    margin-bottom: 0.75rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
  }
}
</style>