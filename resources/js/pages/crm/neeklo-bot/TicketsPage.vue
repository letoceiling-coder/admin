<template>
  <div class="tickets-page">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Тикеты</h1>
        <p class="text-muted-foreground mt-1">Обращения из бота</p>
      </div>
    </div>
    <div class="mb-4">
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
            <th class="px-4 py-3 font-medium">Тема</th>
            <th class="px-4 py-3 font-medium">Сообщение</th>
            <th class="px-4 py-3 font-medium">Статус</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr v-if="loading && !list.length"><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Загрузка…</td></tr>
          <tr v-else-if="!list.length"><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Нет тикетов</td></tr>
          <tr v-for="t in list" :key="t.id" class="bg-card hover:bg-accent/5">
            <td class="px-4 py-3">{{ formatDate(t.created_at) }}</td>
            <td class="px-4 py-3 font-medium">{{ t.subject }}</td>
            <td class="px-4 py-3 max-w-xs truncate">{{ t.message }}</td>
            <td class="px-4 py-3">
              <select
                :value="t.status"
                class="rounded border border-input bg-background px-2 py-1 text-sm"
                @change="updateStatus(t, $event.target.value)"
              >
                <option value="new">Новый</option>
                <option value="in_progress">В работе</option>
                <option value="done">Готово</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="error" class="mt-4 text-sm text-red-600">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import apiClient from '@/api/axios';

const list = ref([]);
const filterStatus = ref('');
const loading = ref(false);
const error = ref('');

function formatDate(v) {
  if (!v) return '—';
  return new Date(v).toLocaleString('ru');
}

async function fetchList() {
  loading.value = true;
  error.value = '';
  try {
    const params = filterStatus.value ? { status: filterStatus.value } : {};
    const { data } = await apiClient.get('/crm/telegram/tickets', { params });
    list.value = data?.data ?? [];
  } catch (e) {
    list.value = [];
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

watch(filterStatus, fetchList);

async function updateStatus(item, status) {
  try {
    await apiClient.put(`/crm/telegram/tickets/${item.id}`, { status });
    item.status = status;
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка';
  }
}

onMounted(fetchList);
</script>
