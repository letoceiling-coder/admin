<template>
  <div class="plan-form-page">
    <div class="mb-6">
      <router-link
        :to="{ name: 'admin.plans' }"
        class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground mb-2"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        К списку планов
      </router-link>
      <h1 class="text-2xl font-bold text-foreground">{{ isEdit ? 'Редактирование плана' : 'Создание плана' }}</h1>
      <p class="text-muted-foreground mt-1">{{ isEdit ? 'Измените данные плана' : 'Добавьте новый тарифный план' }}</p>
    </div>

    <div v-if="loading && isEdit" class="bg-card rounded-lg border border-border p-8 text-center text-muted-foreground">
      Загрузка…
    </div>

    <form
      v-else
      class="bg-card rounded-lg border border-border overflow-hidden max-w-2xl"
      @submit.prevent="submit"
    >
      <div class="p-6 space-y-4">
        <div>
          <label for="plan-name" class="block text-sm font-medium text-foreground mb-1">Название</label>
          <input
            id="plan-name"
            v-model="form.name"
            type="text"
            required
            class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
            placeholder="например, standard"
          />
          <p v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.name }}</p>
        </div>

        <div>
          <label for="plan-cost" class="block text-sm font-medium text-foreground mb-1">Стоимость</label>
          <input
            id="plan-cost"
            v-model.number="form.cost"
            type="number"
            step="0.01"
            min="0"
            required
            class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
            placeholder="0"
          />
          <p v-if="errors.cost" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.cost }}</p>
        </div>

        <div class="flex items-center gap-2">
          <input
            id="plan-active"
            v-model="form.is_active"
            type="checkbox"
            class="rounded border-input"
          />
          <label for="plan-active" class="text-sm font-medium text-foreground">Активен</label>
        </div>

        <div>
          <label for="plan-limits" class="block text-sm font-medium text-foreground mb-1">Ограничения (JSON)</label>
          <textarea
            id="plan-limits"
            v-model="form.limitsJson"
            rows="4"
            class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground font-mono text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
            placeholder='{"max_users": 5, "description": "Стандартный план"}'
          />
          <p class="mt-1 text-xs text-muted-foreground">Необязательно. Пример: {"max_users": 5, "description": "Описание"}</p>
          <p v-if="errors.limits" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.limits }}</p>
        </div>
      </div>

      <div class="px-6 py-4 border-t border-border flex flex-wrap gap-3">
        <button
          type="submit"
          class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
          :disabled="saving"
        >
          {{ saving ? 'Сохранение…' : (isEdit ? 'Сохранить' : 'Создать') }}
        </button>
        <router-link
          :to="{ name: 'admin.plans' }"
          class="rounded-md border border-input bg-background px-4 py-2 text-sm text-foreground hover:bg-accent/10"
        >
          Отмена
        </router-link>
        <template v-if="isEdit">
          <button
            type="button"
            class="ml-auto rounded-md border border-red-600 text-red-600 px-4 py-2 text-sm hover:bg-red-50 dark:hover:bg-red-900/20 disabled:opacity-50"
            :disabled="saving || deleteLoading"
            @click="confirmDelete"
          >
            {{ deleteLoading ? '…' : 'Удалить план' }}
          </button>
        </template>
      </div>
    </form>

    <div v-if="error" class="mt-4 rounded-md bg-red-50 dark:bg-red-900/20 p-4 max-w-2xl">
      <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>

    <!-- Подтверждение удаления на странице редактирования -->
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
      @click.self="showDeleteConfirm = false"
    >
      <div class="bg-card rounded-lg border border-border shadow-lg max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold text-foreground mb-2">Удалить план?</h3>
        <p class="text-muted-foreground text-sm mb-4">
          План «{{ form.name }}» будет удалён. Это действие нельзя отменить.
        </p>
        <div class="flex gap-2 justify-end">
          <button
            type="button"
            class="rounded-md border border-input bg-background px-4 py-2 text-sm text-foreground hover:bg-accent/10"
            @click="showDeleteConfirm = false"
          >
            Отмена
          </button>
          <button
            type="button"
            class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
            :disabled="deleteLoading"
            @click="doDelete"
          >
            {{ deleteLoading ? '…' : 'Удалить' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/axios';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const form = reactive({
  name: '',
  cost: 0,
  is_active: true,
  limitsJson: '',
});
const errors = reactive({ name: '', cost: '', limits: '' });
const loading = ref(false);
const saving = ref(false);
const deleteLoading = ref(false);
const showDeleteConfirm = ref(false);
const error = ref('');

function parseLimitsJson() {
  const s = (form.limitsJson || '').trim();
  if (!s) return null;
  try {
    const v = JSON.parse(s);
    return typeof v === 'object' && v !== null ? v : null;
  } catch {
    return undefined;
  }
}

async function loadPlan() {
  if (!isEdit.value) return;
  loading.value = true;
  error.value = '';
  try {
    const { data } = await apiClient.get(`/admin/plans/${route.params.id}`);
    const p = data?.data;
    if (p) {
      form.name = p.name ?? '';
      form.cost = Number(p.cost) || 0;
      form.is_active = !!p.is_active;
      form.limitsJson = p.limits && typeof p.limits === 'object'
        ? JSON.stringify(p.limits, null, 2)
        : '';
    }
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

function clearErrors() {
  errors.name = '';
  errors.cost = '';
  errors.limits = '';
  error.value = '';
}

async function submit() {
  clearErrors();
  const limits = parseLimitsJson();
  if (form.limitsJson.trim() && limits === undefined) {
    errors.limits = 'Некорректный JSON';
    return;
  }

  saving.value = true;
  try {
    const payload = {
      name: form.name.trim(),
      cost: form.cost,
      is_active: form.is_active,
      limits: limits ?? undefined,
    };
    if (isEdit.value) {
      await apiClient.put(`/admin/plans/${route.params.id}`, payload);
      await loadPlan();
    } else {
      await apiClient.post('/admin/plans', payload);
      router.push({ name: 'admin.plans' });
    }
  } catch (e) {
    const d = e.response?.data;
    if (d?.errors) {
      if (d.errors.name) errors.name = Array.isArray(d.errors.name) ? d.errors.name[0] : d.errors.name;
      if (d.errors.cost) errors.cost = Array.isArray(d.errors.cost) ? d.errors.cost[0] : d.errors.cost;
      if (d.errors.limits) errors.limits = Array.isArray(d.errors.limits) ? d.errors.limits[0] : d.errors.limits;
    }
    error.value = d?.message ?? 'Ошибка сохранения';
  } finally {
    saving.value = false;
  }
}

function confirmDelete() {
  showDeleteConfirm.value = true;
}

async function doDelete() {
  deleteLoading.value = true;
  error.value = '';
  try {
    await apiClient.delete(`/admin/plans/${route.params.id}`);
    showDeleteConfirm.value = false;
    router.push({ name: 'admin.plans' });
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка удаления';
  } finally {
    deleteLoading.value = false;
  }
}

watch(() => route.params.id, () => {
  if (isEdit.value) loadPlan();
  else {
    form.name = '';
    form.cost = 0;
    form.is_active = true;
    form.limitsJson = '';
    clearErrors();
  }
}, { immediate: false });

onMounted(() => {
  if (isEdit.value) loadPlan();
});
</script>
