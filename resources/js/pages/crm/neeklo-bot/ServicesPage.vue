<template>
  <div class="services-page">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Услуги</h1>
        <p class="text-muted-foreground mt-1">Список услуг по категориям</p>
      </div>
      <button type="button" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90" @click="openModal()">
        <span>+</span> Создать
      </button>
    </div>
    <div class="mb-4">
      <label class="text-sm text-muted-foreground">Категория: </label>
      <select v-model="filterCategoryId" class="rounded-md border border-input bg-background px-3 py-1.5 text-sm">
        <option :value="null">Все</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>
    <div class="bg-card rounded-lg border border-border overflow-hidden">
      <table class="w-full text-sm text-left">
        <thead class="bg-muted/50 text-muted-foreground uppercase">
          <tr>
            <th class="px-4 py-3 font-medium">Порядок</th>
            <th class="px-4 py-3 font-medium">Название</th>
            <th class="px-4 py-3 font-medium">Категория</th>
            <th class="px-4 py-3 font-medium">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr v-if="loading && !list.length"><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Загрузка…</td></tr>
          <tr v-else-if="!list.length"><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Нет услуг</td></tr>
          <tr v-for="s in list" :key="s.id" class="bg-card hover:bg-accent/5">
            <td class="px-4 py-3">{{ s.sort_order ?? 0 }}</td>
            <td class="px-4 py-3 font-medium">{{ s.name }}</td>
            <td class="px-4 py-3">{{ s.category?.name ?? '—' }}</td>
            <td class="px-4 py-3">
              <button type="button" class="mr-2 text-primary hover:underline" @click="openModal(s)">Изменить</button>
              <button type="button" class="text-red-600 hover:underline" :disabled="deleteLoading[s.id]" @click="confirmDelete(s)">Удалить</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="error" class="mt-4 text-sm text-red-600">{{ error }}</div>
    <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="modalOpen = false">
      <div class="bg-card rounded-lg border border-border shadow-lg max-w-lg w-full mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">{{ editId ? 'Редактировать услугу' : 'Новая услуга' }}</h3>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium mb-1">Категория</label>
            <select v-model.number="modal.category_id" class="w-full rounded-md border border-input px-3 py-2 text-sm" required>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Название</label>
            <input v-model="modal.name" type="text" class="w-full rounded-md border border-input px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Описание</label>
            <textarea v-model="modal.description" rows="2" class="w-full rounded-md border border-input px-3 py-2 text-sm"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Результат</label>
            <input v-model="modal.result" type="text" class="w-full rounded-md border border-input px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Цена/сроки</label>
            <input v-model="modal.price_or_terms" type="text" class="w-full rounded-md border border-input px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Порядок</label>
            <input v-model.number="modal.sort_order" type="number" min="0" class="w-full rounded-md border border-input px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="flex gap-2 justify-end mt-4">
          <button type="button" class="rounded-md border px-4 py-2 text-sm" @click="modalOpen = false">Отмена</button>
          <button type="button" class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground" :disabled="saving" @click="submitModal">{{ saving ? '…' : 'Сохранить' }}</button>
        </div>
      </div>
    </div>
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="deleteTarget = null">
      <div class="bg-card rounded-lg border border-border shadow-lg max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold mb-2">Удалить услугу «{{ deleteTarget.name }}»?</h3>
        <div class="flex gap-2 justify-end mt-4">
          <button type="button" class="rounded-md border px-4 py-2 text-sm" @click="deleteTarget = null">Отмена</button>
          <button type="button" class="rounded-md bg-red-600 px-4 py-2 text-sm text-white" :disabled="deleteLoading[deleteTarget.id]" @click="doDelete(deleteTarget)">Удалить</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import apiClient from '@/api/axios';

const list = ref([]);
const categories = ref([]);
const filterCategoryId = ref(null);
const loading = ref(false);
const error = ref('');
const modalOpen = ref(false);
const editId = ref(null);
const modal = ref({ category_id: null, name: '', description: '', result: '', price_or_terms: '', sort_order: 0 });
const saving = ref(false);
const deleteLoading = reactive({});
const deleteTarget = ref(null);

async function fetchCategories() {
  try {
    const { data } = await apiClient.get('/crm/telegram/service-categories');
    categories.value = data?.data ?? [];
    if (categories.value.length && !modal.value.category_id) modal.value.category_id = categories.value[0].id;
  } catch (_) {
    categories.value = [];
  }
}

async function fetchList() {
  loading.value = true;
  error.value = '';
  try {
    const params = filterCategoryId.value ? { category_id: filterCategoryId.value } : {};
    const { data } = await apiClient.get('/crm/telegram/services', { params });
    list.value = data?.data ?? [];
  } catch (e) {
    list.value = [];
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

watch(filterCategoryId, fetchList);

function openModal(item = null) {
  editId.value = item?.id ?? null;
  modal.value = {
    category_id: item?.category_id ?? categories.value[0]?.id ?? null,
    name: item?.name ?? '',
    description: item?.description ?? '',
    result: item?.result ?? '',
    price_or_terms: item?.price_or_terms ?? '',
    sort_order: item?.sort_order ?? 0,
  };
  modalOpen.value = true;
}

async function submitModal() {
  saving.value = true;
  error.value = '';
  try {
    if (editId.value) {
      await apiClient.put(`/crm/telegram/services/${editId.value}`, modal.value);
    } else {
      await apiClient.post('/crm/telegram/services', modal.value);
    }
    modalOpen.value = false;
    await fetchList();
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка сохранения';
  } finally {
    saving.value = false;
  }
}

function confirmDelete(item) {
  deleteTarget.value = item;
}

async function doDelete(item) {
  deleteLoading[item.id] = true;
  try {
    await apiClient.delete(`/crm/telegram/services/${item.id}`);
    deleteTarget.value = null;
    await fetchList();
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка удаления';
  } finally {
    deleteLoading[item.id] = false;
  }
}

onMounted(async () => {
  await fetchCategories();
  await fetchList();
});
</script>
