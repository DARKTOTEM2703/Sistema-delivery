import { ref, readonly } from 'vue';

const notifications = ref([]);
let nextId = 1;

export function useNotifications() {
  function addNotification(message, type = 'info', duration = 3000) {
    const id = nextId++;
    const notification = { 
      id, 
      message, 
      type, 
      timestamp: Date.now() 
    };
    
    notifications.value.push(notification);
    
    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id);
      }, duration);
    }
    
    return id;
  }
  
  function removeNotification(id) {
    const index = notifications.value.findIndex(n => n.id === id);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  }
  
  function success(message, duration = 4000) {
    return addNotification(message, 'success', duration);
  }
  
  function error(message, duration = 6000) {
    return addNotification(message, 'error', duration);
  }
  
  function warning(message, duration = 5000) {
    return addNotification(message, 'warning', duration);
  }
  
  function info(message, duration = 3000) {
    return addNotification(message, 'info', duration);
  }
  
  function clearAll() {
    notifications.value = [];
  }
  
  return {
    notifications: readonly(notifications),
    addNotification,
    removeNotification,
    success,
    error,
    warning,
    info,
    clearAll
  };
}