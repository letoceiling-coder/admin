<template>
  <div class="subscribers-page">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Подписчики</h1>
        <p class="text-muted-foreground mt-1">Список подписчиков с пагинацией и фильтрацией</p>
      </div>
    </div>

    <div class="bg-card rounded-lg border border-border overflow-hidden">
      <div class="p-4 border-b border-border flex flex-wrap gap-3">
        <input
          v-model="filters.domain"
          type="text"
          placeholder="Домен"
          class="rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring w-40"
          @input="debouncedFetch"
        />
        <select
          v-model="filters.plan_id"
          class="rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
          @change="applyFilters"
        >
          <option value="">Все планы</option>
          <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
        <select
          v-model="filters.is_active"
          class="rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
          @change="applyFilters"
        >
          <option value="">Любая активность</option>
          <option value="1">Активные</option>
          <option value="0">Неактивные</option>
        </select>
        <button
          type="button"
          class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors"
          :disabled="loading"
          @click="applyFilters"
        >
          {{ loading ? 'Загрузка…' : 'Применить' }}
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-muted/50 text-muted-foreground uppercase">
            <tr>
              <th class="px-4 py-3 font-medium">Домен</th>
              <th class="px-4 py-3 font-medium">Логин</th>
              <th class="px-4 py-3 font-medium">План</th>
              <th class="px-4 py-3 font-medium">Начало</th>
              <th class="px-4 py-3 font-medium">Конец</th>
              <th class="px-4 py-3 font-medium">Активность</th>
              <th class="px-4 py-3 font-medium"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-if="loading && !list.length" class="bg-card">
              <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Загрузка…</td>
            </tr>
            <tr v-else-if="!list.length" class="bg-card">
              <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Нет подписчиков</td>
            </tr>
            <tr
              v-for="s in list"
              :key="s.id"
              class="bg-card hover:bg-accent/5 transition-colors"
            >
              <td class="px-4 py-3 font-medium text-foreground">{{ s.domain }}</td>
              <td class="px-4 py-3 text-foreground">{{ s.login }}</td>
              <td class="px-4 py-3 text-foreground">{{ s.plan?.name ?? '—' }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ formatDate(s.subscription_start) }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ formatDate(s.subscription_end) }}</td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                    s.is_active
                      ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                      : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                  ]"
                >
                  {{ s.is_active ? 'Активен' : 'Неактивен' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <router-link
                  :to="{ name: 'admin.subscriber', params: { id: s.id } }"
                  class="text-primary hover:underline"
                >
                  Подробнее
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        v-if="meta.last_page > 1"
        class="flex flex-wrap items-center justify-between gap-2 border-t border-border px-4 py-3"
      >
        <p class="text-sm text-muted-foreground">
          Показано {{ (meta.current_page - 1) * meta.per_page + 1 }}–{{ Math.min(meta.current_page * meta.per_page, meta.total) }} из {{ meta.total }}
        </p>
        <div class="flex gap-2">
          <button
            type="button"
            class="rounded-md border border-input bg-background px-3 py-1.5 text-sm text-foreground hover:bg-accent/10 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="meta.current_page <= 1 || loading"
            @click="goToPage(meta.current_page - 1)"
          >
            Назад
          </button>
          <button
            type="button"
            class="rounded-md border border-input bg-background px-3 py-1.5 text-sm text-foreground hover:bg-accent/10 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="meta.current_page >= meta.last_page || loading"
            @click="goToPage(meta.current_page + 1)"
          >
            Вперёд
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import apiClient from '@/api/axios';

const list = ref([]);
const plans = ref([]);
const loading = ref(false);
const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
});

const filters = reactive({
  domain: '',
  plan_id: '',
  is_active: '',
});

let debounceTimer = null;

function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    meta.current_page = 1;
    fetchSubscribers();
  }, 300);
}

function applyFilters() {
  meta.current_page = 1;
  fetchSubscribers();
}

async function fetchPlans() {
  try {
    const { data } = await apiClient.get('/admin/plans');
    plans.value = data?.data ?? [];
  } catch {
    plans.value = [];
  }
}

async function fetchSubscribers() {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    params.set('page', String(meta.current_page));
    params.set('per_page', String(meta.per_page));
    if (filters.domain) params.set('domain', filters.domain);
    if (filters.plan_id) params.set('plan_id', filters.plan_id);
    if (filters.is_active !== '') params.set('is_active', filters.is_active);

    const { data } = await apiClient.get('/admin/subscribers?' + params.toString());
    list.value = data?.data ?? [];
    Object.assign(meta, data?.meta ?? {});
  } catch {
    list.value = [];
  } finally {
    loading.value = false;
  }
}

function goToPage(page) {
  meta.current_page = page;
  fetchSubscribers();
}

function formatDate(val) {
  if (!val) return '—';
  return new Date(val).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

onMounted(() => {
  fetchPlans();
  fetchSubscribers();
});
</script>
