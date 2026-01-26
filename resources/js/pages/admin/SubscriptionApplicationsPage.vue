<template>
  <div class="subscription-applications-page">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Заявки на подписку</h1>
        <p class="text-muted-foreground mt-1">Список заявок с пагинацией и фильтрацией</p>
      </div>
    </div>

    <div class="bg-card rounded-lg border border-border overflow-hidden">
      <div class="p-4 border-b border-border flex flex-wrap gap-3 items-center">
        <input
          v-model="filters.domain"
          type="text"
          placeholder="Домен"
          class="rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring w-40"
          @input="debouncedFetch"
        />
        <select
          v-model="filters.status"
          class="rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
          @change="applyFilters"
        >
          <option value="">Все статусы</option>
          <option value="pending">Ожидает</option>
          <option value="approved">Одобрена</option>
          <option value="rejected">Отклонена</option>
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
              <th class="px-4 py-3 font-medium">Имя</th>
              <th class="px-4 py-3 font-medium">Email</th>
              <th class="px-4 py-3 font-medium">Статус</th>
              <th class="px-4 py-3 font-medium">Истекает</th>
              <th class="px-4 py-3 font-medium">Создана</th>
              <th class="px-4 py-3 font-medium">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-if="loading && !list.length" class="bg-card">
              <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Загрузка…</td>
            </tr>
            <tr v-else-if="!list.length" class="bg-card">
              <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Нет заявок</td>
            </tr>
            <tr
              v-for="a in list"
              :key="a.id"
              class="bg-card hover:bg-accent/5 transition-colors"
            >
              <td class="px-4 py-3 font-medium text-foreground">{{ a.domain }}</td>
              <td class="px-4 py-3 text-foreground">{{ a.name }}</td>
              <td class="px-4 py-3 text-foreground">{{ a.email }}</td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                    statusClass(a.status),
                  ]"
                >
                  {{ statusLabel(a.status) }}
                </span>
              </td>
              <td class="px-4 py-3 text-muted-foreground">{{ formatDate(a.expires_at) }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ formatDate(a.created_at) }}</td>
              <td class="px-4 py-3">
                <template v-if="a.status === 'pending' && !isExpired(a.expires_at)">
                  <div class="flex flex-wrap gap-2 items-center">
                    <select
                      v-if="plans.length"
                      v-model="approvePlanId[a.id]"
                      class="rounded border border-input bg-background px-2 py-1 text-xs text-foreground focus:outline-none focus:ring-1 focus:ring-ring w-28"
                    >
                      <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <button
                      type="button"
                      class="rounded bg-green-600 px-2 py-1 text-xs font-medium text-white hover:bg-green-700 disabled:opacity-50"
                      :disabled="actionLoading[a.id]"
                      @click="approve(a)"
                    >
                      {{ actionLoading[a.id] ? '…' : 'Одобрить' }}
                    </button>
                    <button
                      type="button"
                      class="rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700 disabled:opacity-50"
                      :disabled="actionLoading[a.id]"
                      @click="reject(a)"
                    >
                      {{ actionLoading[a.id] ? '…' : 'Отклонить' }}
                    </button>
                  </div>
                </template>
                <span v-else class="text-muted-foreground">—</span>
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

    <div v-if="error" class="mt-4 rounded-md bg-red-50 dark:bg-red-900/20 p-4">
      <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import apiClient from '@/api/axios';

const list = ref([]);
const plans = ref([]);
const loading = ref(false);
const error = ref('');
const actionLoading = reactive({});
const approvePlanId = reactive({});
const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
});

const filters = reactive({
  domain: '',
  status: '',
});

let debounceTimer = null;

function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    meta.current_page = 1;
    fetchApplications();
  }, 300);
}

function applyFilters() {
  meta.current_page = 1;
  fetchApplications();
}

function statusLabel(s) {
  const map = { pending: 'Ожидает', approved: 'Одобрена', rejected: 'Отклонена' };
  return map[s] ?? s;
}

function statusClass(s) {
  const map = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    rejected: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
  };
  return map[s] ?? 'bg-gray-100 text-gray-700';
}

function isExpired(expiresAt) {
  if (!expiresAt) return false;
  return new Date(expiresAt) < new Date();
}

async function fetchPlans() {
  try {
    const { data } = await apiClient.get('/admin/plans');
    plans.value = data?.data ?? [];
  } catch {
    plans.value = [];
  }
}

async function fetchApplications() {
  loading.value = true;
  error.value = '';
  try {
    const params = new URLSearchParams();
    params.set('page', String(meta.current_page));
    params.set('per_page', String(meta.per_page));
    if (filters.domain) params.set('domain', filters.domain);
    if (filters.status) params.set('status', filters.status);

    const { data } = await apiClient.get('/admin/subscription-applications?' + params.toString());
    list.value = data?.data ?? [];
    Object.assign(meta, data?.meta ?? {});
    list.value.forEach((a) => {
      if (a.status === 'pending' && approvePlanId[a.id] == null && plans.value.length) {
        approvePlanId[a.id] = Number(plans.value[0].id);
      }
    });
  } catch (e) {
    list.value = [];
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

function goToPage(page) {
  meta.current_page = page;
  fetchApplications();
}

async function approve(app) {
  actionLoading[app.id] = true;
  error.value = '';
  try {
    const planId = approvePlanId[app.id] ?? plans.value[0]?.id;
    await apiClient.post(`/admin/subscription-applications/${app.id}/approve`, {
      plan_id: planId ? Number(planId) : undefined,
    });
    await fetchApplications();
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка одобрения';
  } finally {
    actionLoading[app.id] = false;
  }
}

async function reject(app) {
  actionLoading[app.id] = true;
  error.value = '';
  try {
    await apiClient.post(`/admin/subscription-applications/${app.id}/reject`);
    await fetchApplications();
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка отклонения';
  } finally {
    actionLoading[app.id] = false;
  }
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
  fetchApplications();
});
</script>
