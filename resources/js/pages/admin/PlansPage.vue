<template>
  <div class="plans-page">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Планы</h1>
        <p class="text-muted-foreground mt-1">Список тарифных планов</p>
      </div>
      <router-link
        :to="{ name: 'admin.plans.create' }"
        class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Создать план
      </router-link>
    </div>

    <div class="bg-card rounded-lg border border-border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-muted/50 text-muted-foreground uppercase">
            <tr>
              <th class="px-4 py-3 font-medium">Название</th>
              <th class="px-4 py-3 font-medium">Стоимость</th>
              <th class="px-4 py-3 font-medium">Активен</th>
              <th class="px-4 py-3 font-medium">Ограничения</th>
              <th class="px-4 py-3 font-medium">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-if="loading && !list.length" class="bg-card">
              <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">Загрузка…</td>
            </tr>
            <tr v-else-if="!list.length" class="bg-card">
              <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">Нет планов</td>
            </tr>
            <tr
              v-for="p in list"
              :key="p.id"
              class="bg-card hover:bg-accent/5 transition-colors"
            >
              <td class="px-4 py-3 font-medium text-foreground">{{ p.name }}</td>
              <td class="px-4 py-3 text-foreground">{{ formatCost(p.cost) }}</td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                    p.is_active
                      ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                      : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                  ]"
                >
                  {{ p.is_active ? 'Да' : 'Нет' }}
                </span>
              </td>
              <td class="px-4 py-3 text-muted-foreground max-w-[200px] truncate" :title="limitsPreview(p.limits)">
                {{ limitsPreview(p.limits) || '—' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-2 items-center">
                  <router-link
                    :to="{ name: 'admin.plans.edit', params: { id: p.id } }"
                    class="text-primary hover:underline"
                  >
                    Изменить
                  </router-link>
                  <button
                    type="button"
                    class="text-red-600 dark:text-red-400 hover:underline disabled:opacity-50"
                    :disabled="deleteLoading[p.id]"
                    @click="confirmDelete(p)"
                  >
                    {{ deleteLoading[p.id] ? '…' : 'Удалить' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="error" class="mt-4 rounded-md bg-red-50 dark:bg-red-900/20 p-4">
      <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>

    <!-- Модальное подтверждение удаления -->
    <div
      v-if="deleteTarget"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
      @click.self="deleteTarget = null"
    >
      <div class="bg-card rounded-lg border border-border shadow-lg max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold text-foreground mb-2">Удалить план?</h3>
        <p class="text-muted-foreground text-sm mb-4">
          План «{{ deleteTarget.name }}» будет удалён. Это действие нельзя отменить.
        </p>
        <div class="flex gap-2 justify-end">
          <button
            type="button"
            class="rounded-md border border-input bg-background px-4 py-2 text-sm text-foreground hover:bg-accent/10"
            @click="deleteTarget = null"
          >
            Отмена
          </button>
          <button
            type="button"
            class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
            :disabled="deleteLoading[deleteTarget.id]"
            @click="doDelete(deleteTarget)"
          >
            {{ deleteLoading[deleteTarget.id] ? '…' : 'Удалить' }}
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
const loading = ref(false);
const error = ref('');
const deleteLoading = reactive({});
const deleteTarget = ref(null);

function limitsPreview(limits) {
  if (!limits || typeof limits !== 'object') return '';
  if (limits.description) return String(limits.description);
  if (limits.max_users != null) return `max_users: ${limits.max_users}`;
  return JSON.stringify(limits);
}

function formatCost(v) {
  if (v == null) return '—';
  return new Intl.NumberFormat('ru-RU', { style: 'decimal', minimumFractionDigits: 2 }).format(Number(v));
}

async function fetchPlans() {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await apiClient.get('/admin/plans');
    list.value = data?.data ?? [];
  } catch (e) {
    list.value = [];
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

function confirmDelete(plan) {
  deleteTarget.value = plan;
}

async function doDelete(plan) {
  deleteLoading[plan.id] = true;
  error.value = '';
  try {
    await apiClient.delete(`/admin/plans/${plan.id}`);
    deleteTarget.value = null;
    await fetchPlans();
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка удаления';
  } finally {
    deleteLoading[plan.id] = false;
  }
}

onMounted(() => {
  fetchPlans();
});
</script>
