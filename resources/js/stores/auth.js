import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import apiClient from '../api/axios';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const loading = ref(false);
  const initialized = ref(false);

  const isAuthenticated = computed(() => !!user.value);
  const hasAdminPanelAccess = computed(() => user.value?.role && ['manager', 'administrator'].includes(user.value.role.name));

  async function ensureCsrf() {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
  }

  async function fetchUser() {
    if (!initialized.value) loading.value = true;
    try {
      const { data } = await apiClient.get('/user');
      user.value = data;
      return data;
    } catch (e) {
      user.value = null;
      throw e;
    } finally {
      loading.value = false;
      initialized.value = true;
    }
  }

  async function login({ email, password, remember = false }) {
    loading.value = true;
    try {
      await ensureCsrf();
      const { data } = await apiClient.post('/login', { email, password, remember });
      user.value = data.user;
      return { success: true, user: data.user };
    } finally {
      loading.value = false;
    }
  }

  async function register({ name, email, password, password_confirmation }) {
    loading.value = true;
    try {
      await ensureCsrf();
      const { data } = await apiClient.post('/register', { name, email, password, password_confirmation });
      user.value = data.user;
      return { success: true, user: data.user };
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    try {
      await apiClient.post('/logout');
    } finally {
      user.value = null;
      initialized.value = false;
    }
  }

  async function forgotPassword({ email }) {
    loading.value = true;
    try {
      await ensureCsrf();
      await apiClient.post('/forgot-password', { email });
    } finally {
      loading.value = false;
    }
  }

  async function resetPassword({ token, email, password, password_confirmation }) {
    loading.value = true;
    try {
      await ensureCsrf();
      await apiClient.post('/reset-password', { token, email, password, password_confirmation });
    } finally {
      loading.value = false;
    }
  }

  function resetState() {
    user.value = null;
    loading.value = false;
    initialized.value = false;
  }

  return {
    user,
    loading,
    initialized,
    isAuthenticated,
    hasAdminPanelAccess,
    ensureCsrf,
    fetchUser,
    login,
    register,
    logout,
    forgotPassword,
    resetPassword,
    resetState,
  };
});
