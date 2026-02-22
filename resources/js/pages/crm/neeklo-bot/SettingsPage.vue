<template>
  <div class="settings-page">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">Настройки бота</h1>
      <p class="text-muted-foreground mt-1">Тексты, баннер, сайт, презентация, контакты</p>
    </div>
    <div v-if="loading && !form" class="text-muted-foreground">Загрузка…</div>
    <form v-else class="bg-card rounded-lg border border-border p-6 max-w-2xl space-y-4" @submit.prevent="save">
      <div>
        <label class="block text-sm font-medium text-foreground mb-1">Приветствие</label>
        <input v-model="form.welcome_text" type="text" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Текст до нажатия Старт" />
      </div>
      <div>
        <label class="block text-sm font-medium text-foreground mb-1">Текст старта</label>
        <input v-model="form.start_text" type="text" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-foreground mb-1">Оффер на главной</label>
        <textarea v-model="form.home_offer_text" rows="3" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-foreground mb-1">ID баннера (file_id Telegram)</label>
        <input v-model="form.home_banner_file_id" type="text" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-foreground mb-1">URL сайта</label>
        <input v-model="form.site_url" type="url" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-foreground mb-1">ID презентации (file_id)</label>
        <input v-model="form.presentation_file_id" type="text" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-foreground mb-1">URL презентации</label>
        <input v-model="form.presentation_url" type="url" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-foreground mb-1">Username менеджера (t.me/...)</label>
        <input v-model="form.manager_username" type="text" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="@username" />
      </div>
      <div>
        <label class="block text-sm font-medium text-foreground mb-1">ID чата для уведомлений (notify_chat_id)</label>
        <input v-model="form.notify_chat_id" type="text" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-foreground mb-1">UTM-шаблон</label>
        <input v-model="form.utm_template" type="text" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
      </div>
      <div v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</div>
      <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90" :disabled="saving">
        {{ saving ? 'Сохранение…' : 'Сохранить' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/axios';

const form = ref(null);
const loading = ref(true);
const saving = ref(false);
const error = ref('');

async function load() {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await apiClient.get('/crm/telegram/settings');
    form.value = { ...data.data };
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка загрузки';
  } finally {
    loading.value = false;
  }
}

async function save() {
  saving.value = true;
  error.value = '';
  try {
    await apiClient.put('/crm/telegram/settings', form.value);
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Ошибка сохранения';
  } finally {
    saving.value = false;
  }
}

onMounted(load);
</script>
