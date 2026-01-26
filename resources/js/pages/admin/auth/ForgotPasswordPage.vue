<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex items-center justify-center px-4">
    <div class="w-full max-w-md space-y-6">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Восстановление пароля</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Введите ваш email для восстановления пароля</p>
      </div>
      <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
        <div v-if="success" class="space-y-4">
          <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 p-4">
            <p class="text-sm text-green-800 dark:text-green-200">
              Ссылка для восстановления пароля отправлена на указанный email.
            </p>
          </div>
          <div class="text-center">
            <router-link
              to="/admin/login"
              class="block w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors"
            >
              Вернуться к входу
            </router-link>
          </div>
        </div>
        <form v-else @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="error" class="rounded-md bg-red-50 dark:bg-red-900/20 p-3 text-sm text-red-800 dark:text-red-200">
            <ul class="list-disc list-inside">
              <li>{{ error }}</li>
            </ul>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autofocus
              class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
              placeholder="your@email.com"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading">Отправить ссылку</span>
            <span v-else>Отправка...</span>
          </button>
        </form>
      </div>
      <p class="text-center text-sm text-gray-600 dark:text-gray-400">
        Вспомнили пароль?
        <router-link to="/admin/login" class="text-primary hover:underline">Войти</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const loading = computed(() => auth.loading);
const error = ref('');
const success = ref(false);
const form = ref({ email: '' });

async function handleSubmit() {
  error.value = '';
  success.value = false;
  try {
    await auth.forgotPassword({ email: form.value.email });
    success.value = true;
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Не удалось отправить ссылку. Проверьте email.';
    const details = e.response?.data?.errors?.email;
    error.value = Array.isArray(details) ? details.join(' ') : msg;
  }
}
</script>
