<template>
  <div class="reviews-page">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">Отзывы</h1>
      <p class="text-muted-foreground mt-1">Модерация отзывов</p>
    </div>
    <div class="flex gap-2 mb-4">
      <button
        type="button"
        :class="['rounded-md px-4 py-2 text-sm font-medium', tab === 'pending' ? 'bg-primary text-primary-foreground' : 'border border-input bg-background']"
        @click="tab = 'pending'; fetchList()"
      >
        На модерации
      </button>
      <button
        type="button"
        :class="['rounded-md px-4 py-2 text-sm font-medium', tab === 'approved' ? 'bg-primary text-primary-foreground' : 'border border-input bg-background']"
        @click="tab = 'approved'; fetchList()"
      >
        Одобренные
      </button>
    </div>
    <div class="bg-card rounded-lg border border-border overflow-hidden">
      <table class="w-full text-sm text-left">
        <thead class="bg-muted/50 text-muted-foreground uppercase">
          <tr>
            <th class="px-4 py-3 font-medium">Автор</th>
            <th class="px-4 py-3 font-medium">Рейтинг</th>
            <th class="px-4 py-3 font-medium">Текст</th>
            <th class="px-4 py-3 font-medium">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr v-if="loading && !list.length"><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Загрузка…</td></tr>
          <tr v-else-if="!list.length"><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Нет отзывов</td></tr>
          <tr v-for="r in list" :key="r.id" class="bg-card hover:bg-accent/5">
            <td class="px-4 py-3">{{ r.author_name || '—' }}</td>
            <td class="px-4 py-3">{{ r.rating }}</td>
            <td class="px-4 py-3 max-w-xs truncate">{{ r.text }}</td>
            <td class="px-4 py-3">
              <template v-if="tab === 'pending'">
                <button type="button" class="mr-2 text-green-600 hover:underline" @click="approve(r)">Одобрить</button>
                <button type="button" class="text-red-600 hover:underline" @click="reject(r)">Отклонить</button>
              </template>
              <template v-else>
                <button type="button" class="text-muted-foreground text-sm">—</button>
              </template>
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
const tab = ref('pending');
const loading = ref(false);
const error = ref('');

async function fetchList() {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await apiClient.get('/crm/telegram/reviews', { params: { status: tab.value } });
    list.value = data?.data ?? [];
  } catch (e) {
    list.value = [];
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

watch(tab, fetchList);

async function approve(item) {
  try {
    await apiClient.post(`/crm/telegram/reviews/${item.id}/approve`);
    await fetchList();
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка';
  }
}

async function reject(item) {
  try {
    await apiClient.post(`/crm/telegram/reviews/${item.id}/reject`);
    await fetchList();
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка';
  }
}

onMounted(fetchList);
</script>
