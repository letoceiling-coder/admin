<template>
  <div class="leads-page">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Лиды</h1>
        <p class="text-muted-foreground mt-1">Заявки из бота</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent/10"
        :disabled="exporting"
        @click="exportCsv"
      >
        {{ exporting ? '…' : 'Экспорт CSV' }}
      </button>
    </div>
    <div class="mb-4 flex gap-2 flex-wrap">
      <select v-model="filterStatus" class="rounded-md border border-input bg-background px-3 py-1.5 text-sm">
        <option value="">Все статусы</option>
        <option value="new">Новые</option>
        <option value="in_progress">В работе</option>
        <option value="done">Готово</option>
      </select>
    </div>
    <div class="bg-card rounded-lg border border-border overflow-hidden">
      <table class="w-full text-sm text-left">
        <thead class="bg-muted/50 text-muted-foreground uppercase">
          <tr>
            <th class="px-4 py-3 font-medium">Дата</th>
            <th class="px-4 py-3 font-medium">Контакт</th>
            <th class="px-4 py-3 font-medium">Сообщение</th>
            <th class="px-4 py-3 font-medium">Статус</th>
            <th class="px-4 py-3 font-medium">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr v-if="loading && !list.length"><td colspan="5" class="px-4 py-8 text-center text-muted-foreground">Загрузка…</td></tr>
          <tr v-else-if="!list.length"><td colspan="5" class="px-4 py-8 text-center text-muted-foreground">Нет лидов</td></tr>
          <tr v-for="l in list" :key="l.id" class="bg-card hover:bg-accent/5">
            <td class="px-4 py-3">{{ formatDate(l.created_at) }}</td>
            <td class="px-4 py-3">{{ l.name || l.contact || '—' }}</td>
            <td class="px-4 py-3 max-w-xs truncate">{{ l.message || '—' }}</td>
            <td class="px-4 py-3">
              <select
                :value="l.status"
                class="rounded border border-input bg-background px-2 py-1 text-sm"
                @change="updateStatus(l, $event.target.value)"
              >
                <option value="new">Новый</option>
                <option value="in_progress">В работе</option>
                <option value="done">Готово</option>
              </select>
            </td>
            <td class="px-4 py-3">—</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="meta.last_page > 1" class="mt-4 flex gap-2 items-center">
      <button type="button" class="rounded border px-3 py-1 text-sm" :disabled="meta.current_page <= 1" @click="page = meta.current_page - 1; fetchList()">Назад</button>
      <span class="text-sm text-muted-foreground">{{ meta.current_page }} / {{ meta.last_page }}</span>
      <button type="button" class="rounded border px-3 py-1 text-sm" :disabled="meta.current_page >= meta.last_page" @click="page = meta.current_page + 1; fetchList()">Вперёд</button>
    </div>
    <div v-if="error" class="mt-4 text-sm text-red-600">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import apiClient from '@/api/axios';

const list = ref([]);
const meta = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
const page = ref(1);
const filterStatus = ref('');
const loading = ref(false);
const exporting = ref(false);
const error = ref('');

async function exportCsv() {
  exporting.value = true;
  error.value = '';
  try {
    const params = filterStatus.value ? { status: filterStatus.value } : {};
    const { data } = await apiClient.get('/crm/telegram/leads/export.csv', { params, responseType: 'blob' });
    const url = URL.createObjectURL(data);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'telegram_leads_' + new Date().toISOString().slice(0, 10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка экспорта';
  } finally {
    exporting.value = false;
  }
}

function formatDate(v) {
  if (!v) return '—';
  return new Date(v).toLocaleString('ru');
}

async function fetchList() {
  loading.value = true;
  error.value = '';
  try {
    const params = { page: page.value, per_page: 15 };
    if (filterStatus.value) params.status = filterStatus.value;
    const { data } = await apiClient.get('/crm/telegram/leads', { params });
    list.value = data?.data ?? [];
    meta.value = data?.meta ?? meta.value;
  } catch (e) {
    list.value = [];
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

watch([filterStatus, page], fetchList);

async function updateStatus(item, status) {
  try {
    await apiClient.put(`/crm/telegram/leads/${item.id}`, { status });
    item.status = status;
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка';
  }
}

onMounted(fetchList);
</script>
