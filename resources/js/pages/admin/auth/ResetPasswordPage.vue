<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex items-center justify-center px-4">
    <div class="w-full max-w-md space-y-6">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Сброс пароля</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Введите новый пароль</p>
      </div>
      <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="error" class="rounded-md bg-red-50 dark:bg-red-900/20 p-3 text-sm text-red-800 dark:text-red-200">
            <ul class="list-disc list-inside">
              <li>{{ error }}</li>
            </ul>
          </div>
          <div v-if="status" class="rounded-md bg-green-50 dark:bg-green-900/20 p-3 text-sm text-green-800 dark:text-green-200">
            {{ status }}
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autofocus
              readonly
              class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white transition-colors cursor-not-allowed"
              placeholder="your@email.com"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Новый пароль</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
              placeholder="Минимум 8 символов"
            />
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Подтверждение пароля</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
              placeholder="Повторите пароль"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading">Изменить пароль</span>
            <span v-else>Изменение...</span>
          </button>
        </form>
      </div>
      <p class="text-center text-sm text-gray-600 dark:text-gray-400">
        <router-link to="/admin/login" class="text-primary hover:underline">Вернуться к входу</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const loading = computed(() => auth.loading);
const error = ref('');
const status = ref('');
const form = ref({
  email: '',
  password: '',
  password_confirmation: '',
  token: '',
});

onMounted(() => {
  form.value.token = route.query.token || '';
  form.value.email = route.query.email || '';
});

function formatValidationErrors(errors) {
  if (!errors || typeof errors !== 'object') return null;
  return Object.values(errors).flat().filter(Boolean).join(' ');
}

async function handleSubmit() {
  error.value = '';
  status.value = '';
  try {
    await auth.resetPassword({
      token: form.value.token,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
    });
    status.value = 'Пароль успешно изменён. Можно войти с новым паролем.';
    setTimeout(() => router.push({ name: 'admin.login' }), 2000);
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Не удалось сбросить пароль.';
    const details = formatValidationErrors(e.response?.data?.errors);
    error.value = details ? `${msg} ${details}` : msg;
  }
}
</script>
