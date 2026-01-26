import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import apiClient from '../api/axios';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('auth_token') || null);
  const loading = ref(false);
  const initialized = ref(false);

  const isAuthenticated = computed(() => !!user.value && !!token.value);
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
      // Если ошибка 401, удаляем токен
      if (e.response?.status === 401) {
        token.value = null;
        localStorage.removeItem('auth_token');
      }
      throw e;
    } finally {
      loading.value = false;
      initialized.value = true;
    }
  }

  async function login({ email, password, remember = false }) {
    loading.value = true;
    try {
      const { data } = await apiClient.post('/login', { email, password, remember });
      user.value = data.user;
      if (data.token) {
        token.value = data.token;
        localStorage.setItem('auth_token', data.token);
      }
      return { success: true, user: data.user };
    } finally {
      loading.value = false;
    }
  }

  async function register({ name, email, password, password_confirmation }) {
    loading.value = true;
    try {
      const { data } = await apiClient.post('/register', { name, email, password, password_confirmation });
      user.value = data.user;
      if (data.token) {
        token.value = data.token;
        localStorage.setItem('auth_token', data.token);
      }
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
      token.value = null;
      localStorage.removeItem('auth_token');
      initialized.value = false;
    }
  }

  async function forgotPassword({ email }) {
    loading.value = true;
    try {
      await apiClient.post('/forgot-password', { email });
    } finally {
      loading.value = false;
    }
  }

  async function resetPassword({ token, email, password, password_confirmation }) {
    loading.value = true;
    try {
      await apiClient.post('/reset-password', { token, email, password, password_confirmation });
    } finally {
      loading.value = false;
    }
  }

  function resetState() {
    user.value = null;
    token.value = null;
    localStorage.removeItem('auth_token');
    loading.value = false;
    initialized.value = false;
  }

  return {
    user,
    token,
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
