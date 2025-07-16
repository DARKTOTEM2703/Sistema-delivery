import { reactive } from "vue";

const notifications = reactive([]);
let notificationId = 0;
// Agregar un mapa para rastrear mensajes recientes y evitar duplicados
const recentNotifications = new Map();

export function useNotifications() {
  const addNotification = (type, message, duration = 4000) => {
    // Verificar si este mensaje exacto se mostró recientemente
    const key = `${type}:${message}`;
    const now = Date.now();

    // Si el mismo mensaje se mostró en los últimos 2.5 segundos, ignorarlo
    if (recentNotifications.has(key)) {
      const lastTime = recentNotifications.get(key);
      if (now - lastTime < 2500) {
        // 2.5 segundos
        console.log("Evitando notificación duplicada:", message);
        return null; // Evitar duplicados
      }
    }

    // Registrar este mensaje
    recentNotifications.set(key, now);

    // Limpiar mensajes antiguos del mapa
    setTimeout(() => {
      recentNotifications.delete(key);
    }, 5000);

    const id = notificationId++;
    const notification = { id, type, message };

    notifications.push(notification);

    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id);
      }, duration);
    }

    return id;
  };

  const removeNotification = (id) => {
    const index = notifications.findIndex((n) => n.id === id);
    if (index !== -1) {
      notifications.splice(index, 1);
    }
  };

  const success = (message, duration) =>
    addNotification("success", message, duration);
  const error = (message, duration) =>
    addNotification("error", message, duration);
  const warning = (message, duration) =>
    addNotification("warning", message, duration);
  const info = (message, duration) =>
    addNotification("info", message, duration);

  return {
    notifications,
    addNotification,
    removeNotification,
    success,
    error,
    warning,
    info,
  };
}
