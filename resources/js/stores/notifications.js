import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import apiClient from '../api/axios';

/**
 * Store уведомлений.
 * Список, счётчик непрочитанных, загрузка с API.
 * @see https://pinia.vuejs.org/
 */
export const useNotificationsStore = defineStore('notifications', () => {
  const list = ref([]);
  const loading = ref(false);

  const unreadCount = computed(() => list.value.filter((n) => !n.read).length);

  async function fetchNotifications() {
    loading.value = true;
    try {
      const { data } = await apiClient.get('/notifications');
      list.value = Array.isArray(data?.data) ? data.data : [];
      return list.value;
    } catch (e) {
      list.value = [];
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function markAsRead(id) {
    try {
      await apiClient.patch(`/notifications/${id}/read`);
      const n = list.value.find((x) => x.id === id);
      if (n) n.read = true;
    } catch (e) {
      throw e;
    }
  }

  function reset() {
    list.value = [];
    loading.value = false;
  }

  return {
    list,
    loading,
    unreadCount,
    fetchNotifications,
    markAsRead,
    reset,
  };
});
