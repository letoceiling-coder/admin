<template>
  <div class="cases-page">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Кейсы</h1>
        <p class="text-muted-foreground mt-1">Список кейсов</p>
      </div>
      <button type="button" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90" @click="openModal()">+ Создать</button>
    </div>
    <div class="bg-card rounded-lg border border-border overflow-hidden">
      <table class="w-full text-sm text-left">
        <thead class="bg-muted/50 text-muted-foreground uppercase">
          <tr>
            <th class="px-4 py-3 font-medium">Порядок</th>
            <th class="px-4 py-3 font-medium">Название</th>
            <th class="px-4 py-3 font-medium">Теги</th>
            <th class="px-4 py-3 font-medium">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr v-if="loading && !list.length"><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Загрузка…</td></tr>
          <tr v-else-if="!list.length"><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Нет кейсов</td></tr>
          <tr v-for="c in list" :key="c.id" class="bg-card hover:bg-accent/5">
            <td class="px-4 py-3">{{ c.sort_order ?? 0 }}</td>
            <td class="px-4 py-3 font-medium">{{ c.title }}</td>
            <td class="px-4 py-3">{{ Array.isArray(c.tags) ? c.tags.join(', ') : '—' }}</td>
            <td class="px-4 py-3">
              <button type="button" class="mr-2 text-primary hover:underline" @click="openMedia(c.id)">Медиа</button>
              <button type="button" class="mr-2 text-primary hover:underline" @click="openModal(c)">Изменить</button>
              <button type="button" class="text-red-600 hover:underline" :disabled="deleteLoading[c.id]" @click="confirmDelete(c)">Удалить</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="error" class="mt-4 text-sm text-red-600">{{ error }}</div>
    <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="modalOpen = false">
      <div class="bg-card rounded-lg border border-border shadow-lg max-w-lg w-full mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">{{ editId ? 'Редактировать кейс' : 'Новый кейс' }}</h3>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium mb-1">Название</label>
            <input v-model="modal.title" type="text" class="w-full rounded-md border border-input px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Задача</label>
            <textarea v-model="modal.task" rows="2" class="w-full rounded-md border border-input px-3 py-2 text-sm"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Решение</label>
            <textarea v-model="modal.solution" rows="2" class="w-full rounded-md border border-input px-3 py-2 text-sm"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Результат</label>
            <textarea v-model="modal.result" rows="2" class="w-full rounded-md border border-input px-3 py-2 text-sm"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Теги (через запятую)</label>
            <input v-model="modal.tagsStr" type="text" class="w-full rounded-md border border-input px-3 py-2 text-sm" />
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
    <div v-if="mediaCaseId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeMedia">
      <div class="bg-card rounded-lg border border-border shadow-lg max-w-lg w-full mx-4 p-6">
        <h3 class="text-lg font-semibold mb-4">Медиа кейса</h3>
        <div class="space-y-2 mb-4">
          <div v-for="m in mediaList" :key="m.id" class="flex items-center justify-between rounded border px-3 py-2">
            <span class="text-sm truncate">{{ m.file_id }} ({{ m.type }})</span>
            <button type="button" class="text-red-600 text-sm" @click="deleteMedia(m)">Удалить</button>
          </div>
          <p v-if="mediaList.length === 0" class="text-muted-foreground text-sm">Нет медиа</p>
        </div>
        <div class="flex gap-2 items-end">
          <div class="flex-1">
            <label class="block text-sm font-medium mb-1">file_id</label>
            <input v-model="newMediaFileId" type="text" class="w-full rounded-md border border-input px-3 py-2 text-sm" placeholder="Telegram file_id" />
          </div>
          <button type="button" class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground" @click="addMedia">Добавить</button>
        </div>
        <div class="flex justify-end mt-4">
          <button type="button" class="rounded-md border px-4 py-2 text-sm" @click="closeMedia">Закрыть</button>
        </div>
      </div>
    </div>
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="deleteTarget = null">
      <div class="bg-card rounded-lg border border-border shadow-lg max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold mb-2">Удалить кейс «{{ deleteTarget.title }}»?</h3>
        <div class="flex gap-2 justify-end mt-4">
          <button type="button" class="rounded-md border px-4 py-2 text-sm" @click="deleteTarget = null">Отмена</button>
          <button type="button" class="rounded-md bg-red-600 px-4 py-2 text-sm text-white" :disabled="deleteLoading[deleteTarget.id]" @click="doDelete(deleteTarget)">Удалить</button>
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
const modalOpen = ref(false);
const editId = ref(null);
const modal = ref({ title: '', task: '', solution: '', result: '', tagsStr: '', sort_order: 0 });
const saving = ref(false);
const deleteLoading = reactive({});
const deleteTarget = ref(null);
const mediaCaseId = ref(null);
const mediaList = ref([]);
const newMediaFileId = ref('');

async function fetchList() {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await apiClient.get('/crm/telegram/cases');
    list.value = data?.data ?? [];
  } catch (e) {
    list.value = [];
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

function openMedia(caseId) {
  mediaCaseId.value = caseId;
  loadMedia(caseId);
}

function closeMedia() {
  mediaCaseId.value = null;
}

async function loadMedia(caseId) {
  try {
    const { data } = await apiClient.get(`/crm/telegram/cases/${caseId}/media`);
    mediaList.value = data?.data ?? [];
  } catch (_) {
    mediaList.value = [];
  }
}

async function addMedia() {
  if (!newMediaFileId.value || !mediaCaseId.value) return;
  try {
    await apiClient.post(`/crm/telegram/cases/${mediaCaseId.value}/media`, { file_id: newMediaFileId.value, type: 'photo' });
    newMediaFileId.value = '';
    await loadMedia(mediaCaseId.value);
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка';
  }
}

async function deleteMedia(m) {
  try {
    await apiClient.delete(`/crm/telegram/case-media/${m.id}`);
    await loadMedia(mediaCaseId.value);
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка';
  }
}

function openModal(item = null) {
  editId.value = item?.id ?? null;
  modal.value = {
    title: item?.title ?? '',
    task: item?.task ?? '',
    solution: item?.solution ?? '',
    result: item?.result ?? '',
    tagsStr: Array.isArray(item?.tags) ? item.tags.join(', ') : '',
    sort_order: item?.sort_order ?? 0,
  };
  modalOpen.value = true;
}

async function submitModal() {
  saving.value = true;
  error.value = '';
  const payload = {
    title: modal.value.title,
    task: modal.value.task,
    solution: modal.value.solution,
    result: modal.value.result,
    tags: modal.value.tagsStr ? modal.value.tagsStr.split(',').map(s => s.trim()).filter(Boolean) : [],
    sort_order: modal.value.sort_order,
  };
  try {
    if (editId.value) {
      await apiClient.put(`/crm/telegram/cases/${editId.value}`, payload);
    } else {
      await apiClient.post('/crm/telegram/cases', payload);
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
    await apiClient.delete(`/crm/telegram/cases/${item.id}`);
    deleteTarget.value = null;
    await fetchList();
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка удаления';
  } finally {
    deleteLoading[item.id] = false;
  }
}

onMounted(fetchList);
</script>
