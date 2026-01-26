<template>
  <div class="subscriber-detail-page">
    <div class="mb-6">
      <router-link
        :to="{ name: 'admin.subscribers' }"
        class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground mb-2"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        К списку подписчиков
      </router-link>
      <h1 class="text-2xl font-bold text-foreground">Подписчик</h1>
      <p class="text-muted-foreground mt-1">{{ subscriber?.domain ?? '—' }}</p>
    </div>

    <div v-if="loading && !subscriber" class="bg-card rounded-lg border border-border p-8 text-center text-muted-foreground">
      Загрузка…
    </div>
    <div v-else-if="error" class="bg-card rounded-lg border border-border p-6">
      <p class="text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
    <div v-else-if="subscriber" class="space-y-6">
      <div class="bg-card rounded-lg border border-border overflow-hidden">
        <div class="px-4 py-3 border-b border-border bg-muted/30">
          <h2 class="font-semibold text-foreground">Основные данные</h2>
        </div>
        <dl class="divide-y divide-border">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-4 py-3">
            <dt class="text-sm text-muted-foreground">Домен</dt>
            <dd class="sm:col-span-2 text-foreground font-medium">{{ subscriber.domain }}</dd>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-4 py-3">
            <dt class="text-sm text-muted-foreground">Логин</dt>
            <dd class="sm:col-span-2 text-foreground">{{ subscriber.login }}</dd>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-4 py-3">
            <dt class="text-sm text-muted-foreground">План</dt>
            <dd class="sm:col-span-2 text-foreground">{{ subscriber.plan?.name ?? '—' }}</dd>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-4 py-3">
            <dt class="text-sm text-muted-foreground">Начало подписки</dt>
            <dd class="sm:col-span-2 text-foreground">{{ formatDate(subscriber.subscription_start) }}</dd>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-4 py-3">
            <dt class="text-sm text-muted-foreground">Конец подписки</dt>
            <dd class="sm:col-span-2 text-foreground">{{ formatDate(subscriber.subscription_end) }}</dd>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-4 py-3">
            <dt class="text-sm text-muted-foreground">Активность</dt>
            <dd class="sm:col-span-2">
              <span
                :class="[
                  'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                  subscriber.is_active
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                ]"
              >
                {{ subscriber.is_active ? 'Активен' : 'Неактивен' }}
              </span>
            </dd>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-4 py-3">
            <dt class="text-sm text-muted-foreground">API токен</dt>
            <dd class="sm:col-span-2 font-mono text-sm text-foreground break-all">
              {{ maskedToken(subscriber.api_token) }}
            </dd>
          </div>
        </dl>
      </div>

      <div v-if="hasPaymentData" class="bg-card rounded-lg border border-border overflow-hidden">
        <div class="px-4 py-3 border-b border-border bg-muted/30">
          <h2 class="font-semibold text-foreground">Данные об оплате</h2>
        </div>
        <pre class="p-4 text-sm text-foreground overflow-x-auto">{{ JSON.stringify(subscriber.payment_data, null, 2) }}</pre>
      </div>

      <div class="bg-card rounded-lg border border-border overflow-hidden">
        <div class="px-4 py-3 border-b border-border bg-muted/30">
          <h2 class="font-semibold text-foreground">Даты</h2>
        </div>
        <dl class="divide-y divide-border">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-4 py-3">
            <dt class="text-sm text-muted-foreground">Создан</dt>
            <dd class="sm:col-span-2 text-foreground">{{ formatDateTime(subscriber.created_at) }}</dd>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-4 py-3">
            <dt class="text-sm text-muted-foreground">Обновлён</dt>
            <dd class="sm:col-span-2 text-foreground">{{ formatDateTime(subscriber.updated_at) }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import apiClient from '@/api/axios';

const route = useRoute();
const subscriber = ref(null);
const loading = ref(false);
const error = ref('');

const hasPaymentData = computed(() => {
  const pd = subscriber.value?.payment_data;
  return pd && typeof pd === 'object' && Object.keys(pd).length > 0;
});

async function fetchSubscriber() {
  const id = route.params.id;
  if (!id) return;
  loading.value = true;
  error.value = '';
  try {
    const { data } = await apiClient.get('/admin/subscribers/' + id);
    subscriber.value = data?.data ?? null;
    if (!subscriber.value) error.value = 'Подписчик не найден';
  } catch (e) {
    subscriber.value = null;
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

function maskedToken(token) {
  if (!token || typeof token !== 'string') return '—';
  if (token.length <= 12) return '••••••••';
  return token.slice(0, 8) + '••••••••' + token.slice(-4);
}

function formatDate(val) {
  if (!val) return '—';
  return new Date(val).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function formatDateTime(val) {
  if (!val) return '—';
  return new Date(val).toLocaleString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

watch(() => route.params.id, fetchSubscriber, { immediate: true });
</script>
