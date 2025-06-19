<template>
  <div class="toast" :class="{ 'show': show }">
    <div class="toast-content">
      <span>{{ message }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  message: String
});
</script>

<style scoped>
.toast {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%) translateY(100px);
  background-color: var(--card-bg);
  color: var(--text-color);
  padding: 12px 20px;
  border-radius: 6px;
  opacity: 0;
  transition: all 0.3s;
  z-index: 1000;
  border: 1px solid var(--border-color);
  box-shadow: var(--box-shadow);
}

.toast.show {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}
</style>