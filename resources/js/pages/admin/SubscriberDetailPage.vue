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

      <div class="bg-card rounded-lg border border-border overflow-hidden">
        <div class="px-4 py-3 border-b border-border bg-muted/30">
          <h2 class="font-semibold text-foreground">Управление диапазоном подписки</h2>
        </div>
        <form class="p-6 space-y-4 max-w-xl" @submit.prevent="saveRange">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
            <label for="sub-start" class="text-sm font-medium text-foreground">Начало</label>
            <div class="sm:col-span-2">
              <input
                id="sub-start"
                v-model="rangeForm.subscription_start"
                type="date"
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
              />
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
            <label for="sub-end" class="text-sm font-medium text-foreground">Конец</label>
            <div class="sm:col-span-2">
              <input
                id="sub-end"
                v-model="rangeForm.subscription_end"
                type="date"
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
              />
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
            <span class="text-sm font-medium text-foreground">Активен</span>
            <div class="sm:col-span-2 space-y-1">
              <div class="flex items-center gap-2">
                <input
                  id="sub-active"
                  v-model="rangeForm.is_active"
                  type="checkbox"
                  class="rounded border-input disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="isSubscriptionEndPast"
                />
                <label for="sub-active" class="text-sm text-muted-foreground">Подписка активна</label>
              </div>
              <p v-if="isSubscriptionEndPast" class="text-xs text-amber-600 dark:text-amber-400">
                Подписка не может быть активной: срок действия истёк.
              </p>
            </div>
          </div>
          <div class="flex flex-wrap gap-2 pt-2">
            <button
              type="submit"
              class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50 transition-colors"
              :disabled="saving || (isSubscriptionEndPast && rangeForm.is_active)"
            >
              {{ saving ? 'Сохранение…' : 'Сохранить' }}
            </button>
          </div>
        </form>
        <div v-if="rangeError || rangeSuccess" class="px-6 pb-4 space-y-1">
          <p v-if="rangeError" class="text-sm text-red-600 dark:text-red-400">{{ rangeError }}</p>
          <p v-if="rangeSuccess" class="text-sm text-green-600 dark:text-green-400">{{ rangeSuccess }}</p>
        </div>
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
import { ref, reactive, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import apiClient from '@/api/axios';

const route = useRoute();
const subscriber = ref(null);
const loading = ref(false);
const error = ref('');
const saving = ref(false);
const rangeError = ref('');
const rangeSuccess = ref('');

const rangeForm = reactive({
  subscription_start: '',
  subscription_end: '',
  is_active: true,
});

const hasPaymentData = computed(() => {
  const pd = subscriber.value?.payment_data;
  return pd && typeof pd === 'object' && Object.keys(pd).length > 0;
});

const isSubscriptionEndPast = computed(() => {
  const end = rangeForm.subscription_end;
  if (!end || typeof end !== 'string') return false;
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const endDate = new Date(end);
  if (isNaN(endDate.getTime())) return false;
  endDate.setHours(0, 0, 0, 0);
  return endDate < today;
});

function toYMD(val) {
  if (!val) return '';
  const d = new Date(val);
  if (isNaN(d.getTime())) return '';
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

function syncRangeForm() {
  const s = subscriber.value;
  if (!s) return;
  rangeForm.subscription_start = toYMD(s.subscription_start);
  rangeForm.subscription_end = toYMD(s.subscription_end);
  const endPast = isEndDatePast(toYMD(s.subscription_end));
  rangeForm.is_active = endPast ? false : !!s.is_active;
  rangeError.value = '';
  rangeSuccess.value = '';
}

function isEndDatePast(ymd) {
  if (!ymd || typeof ymd !== 'string') return false;
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const d = new Date(ymd);
  if (isNaN(d.getTime())) return false;
  d.setHours(0, 0, 0, 0);
  return d < today;
}

async function fetchSubscriber() {
  const id = route.params.id;
  if (!id) return;
  loading.value = true;
  error.value = '';
  try {
    const { data } = await apiClient.get('/admin/subscribers/' + id);
    subscriber.value = data?.data ?? null;
    if (!subscriber.value) error.value = 'Подписчик не найден';
    else syncRangeForm();
  } catch (e) {
    subscriber.value = null;
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

async function saveRange() {
  const id = route.params.id;
  if (!id || !subscriber.value) return;
  saving.value = true;
  rangeError.value = '';
  rangeSuccess.value = '';
  try {
    const payload = {
      subscription_start: rangeForm.subscription_start || null,
      subscription_end: rangeForm.subscription_end || null,
      is_active: isSubscriptionEndPast.value ? false : rangeForm.is_active,
    };
    const { data } = await apiClient.put('/admin/subscribers/' + id, payload);
    subscriber.value = data?.data ?? subscriber.value;
    rangeSuccess.value = 'Диапазон подписки обновлён.';
  } catch (e) {
    const d = e.response?.data;
    rangeError.value = d?.message ?? (d?.errors ? Object.values(d.errors).flat().join(' ') : 'Ошибка сохранения');
  } finally {
    saving.value = false;
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

watch(
  () => rangeForm.subscription_end,
  (end) => {
    if (isEndDatePast(end) && rangeForm.is_active) {
      rangeForm.is_active = false;
    }
  }
);
</script>
