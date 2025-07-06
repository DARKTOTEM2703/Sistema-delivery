import { ref, reactive } from "vue";

const notifications = reactive([]);

let notificationId = 0;

export function useNotifications() {
  const addNotification = (type, message, duration = 4000) => {
    const id = ++notificationId;
    const notification = {
      id,
      type,
      message,
      duration,
    };

    notifications.push(notification);

    // Auto-remover después del duration
    setTimeout(() => {
      removeNotification(id);
    }, duration);

    return id;
  };

  const removeNotification = (id) => {
    const index = notifications.findIndex((n) => n.id === id);
    if (index > -1) {
      notifications.splice(index, 1);
    }
  };

  const success = (message, duration) => {
    return addNotification("success", message, duration);
  };

  const error = (message, duration) => {
    return addNotification("error", message, duration);
  };

  const warning = (message, duration) => {
    return addNotification("warning", message, duration);
  };

  const info = (message, duration) => {
    return addNotification("info", message, duration);
  };

  return {
    notifications,
    success,
    error,
    warning,
    info,
    removeNotification,
  };
}
