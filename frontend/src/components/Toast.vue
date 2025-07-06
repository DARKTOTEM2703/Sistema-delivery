<template>
  <Teleport to="body">
    <div class="toast-container">
      <transition-group name="toast" tag="div">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="['toast', `toast-${notification.type}`]"
          @click="removeNotification(notification.id)"
        >
          <div class="toast-icon">
            {{ getIcon(notification.type) }}
          </div>
          <div class="toast-message">
            {{ notification.message }}
          </div>
          <button 
            class="toast-close"
            @click="removeNotification(notification.id)"
          >
            ×
          </button>
        </div>
      </transition-group>
    </div>
  </Teleport>
</template>

<script setup>
import { inject } from 'vue'

const { notifications, removeNotification } = inject('notifications')

function getIcon(type) {
  const icons = {
    success: '✅',
    error: '❌',
    warning: '⚠️',
    info: 'ℹ️'
  }
  return icons[type] || 'ℹ️'
}
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  pointer-events: none;
}

.toast {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(8px);
  pointer-events: auto;
  cursor: pointer;
  max-width: 400px;
  min-width: 300px;
}

.toast-success {
  background: rgba(16, 185, 129, 0.9);
  color: white;
  border-left: 4px solid #10b981;
}

.toast-error {
  background: rgba(239, 68, 68, 0.9);
  color: white;
  border-left: 4px solid #ef4444;
}

.toast-warning {
  background: rgba(245, 158, 11, 0.9);
  color: white;
  border-left: 4px solid #f59e0b;
}

.toast-info {
  background: rgba(59, 130, 246, 0.9);
  color: white;
  border-left: 4px solid #3b82f6;
}

.toast-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.toast-message {
  flex: 1;
  font-weight: 500;
}

.toast-close {
  background: none;
  border: none;
  color: currentColor;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.toast-close:hover {
  opacity: 1;
}

/* Animaciones */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}

@media (max-width: 640px) {
  .toast-container {
    top: 0.5rem;
    right: 0.5rem;
    left: 0.5rem;
  }
  
  .toast {
    min-width: auto;
    max-width: none;
  }
}
</style>