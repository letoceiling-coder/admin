<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex items-center justify-center px-4">
    <div class="w-full max-w-md space-y-6">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Вход в систему</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Введите ваши учетные данные</p>
      </div>
      <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
        <form @submit.prevent="handleSubmit" class="space-y-4">
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

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Пароль</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
              placeholder="••••••••"
            />
          </div>

          <div class="flex items-center">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
              Запомнить меня
            </label>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading">Войти</span>
            <span v-else>Вход...</span>
          </button>
        </form>
      </div>
      <div class="text-center space-y-2">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Нет аккаунта?
          <router-link to="/admin/register" class="text-primary hover:underline">Зарегистрироваться</router-link>
        </p>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Забыли пароль?
          <router-link to="/admin/forgot-password" class="text-primary hover:underline">Восстановить</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();
const loading = computed(() => auth.loading);
const error = ref('');
const form = ref({
  email: '',
  password: '',
  remember: false,
});

async function handleSubmit() {
  error.value = '';
  try {
    await auth.login({
      email: form.value.email,
      password: form.value.password,
      remember: form.value.remember,
    });
    const redirect = route.query.redirect;
    if (auth.hasAdminPanelAccess) {
      router.push(redirect && String(redirect).startsWith('/admin') ? redirect : { name: 'admin.dashboard' });
    } else {
      router.push({ path: '/' });
    }
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Ошибка входа. Проверьте данные.';
    error.value = msg;
  }
}
</script>
