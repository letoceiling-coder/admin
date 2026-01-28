<template>
  <div class="commercial-proposal-page">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Рассылка коммерческого предложения</h1>
        <p class="text-muted-foreground mt-1">Отправка КП на указанный email с предпросмотром</p>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
      <div class="space-y-4">
        <div class="bg-card rounded-lg border border-border p-4 sm:p-6">
          <h2 class="text-lg font-semibold text-foreground mb-4">Отправить КП</h2>
          <form @submit.prevent="send" class="space-y-4">
            <div>
              <label for="email" class="block text-sm font-medium text-foreground mb-1">Email получателя</label>
              <input
                id="email"
                v-model="email"
                type="email"
                required
                placeholder="client@example.com"
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
              />
              <p v-if="emailError" class="mt-1 text-sm text-destructive">{{ emailError }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="sending"
              >
                <svg v-if="sending" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                {{ sending ? 'Отправка…' : 'Отправить КП' }}
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-accent/10 transition-colors"
                :disabled="loadingPreview"
                @click="loadPreview"
              >
                {{ loadingPreview ? 'Загрузка…' : 'Обновить предпросмотр' }}
              </button>
            </div>
          </form>
          <p v-if="message" :class="['mt-3 text-sm', messageSuccess ? 'text-green-600 dark:text-green-400' : 'text-destructive']">
            {{ message }}
          </p>
        </div>
      </div>

      <div class="bg-card rounded-lg border border-border overflow-hidden flex flex-col">
        <div class="p-4 border-b border-border flex items-center justify-between">
          <h2 class="text-lg font-semibold text-foreground">Предпросмотр письма</h2>
          <span v-if="loadingPreview" class="text-sm text-muted-foreground">Загрузка…</span>
        </div>
        <div class="flex-1 min-h-[400px] bg-muted/30 overflow-auto p-4">
          <iframe
            v-if="previewHtml"
            ref="previewFrame"
            title="Предпросмотр коммерческого предложения"
            class="w-full min-h-[500px] border-0 rounded-lg bg-white shadow-sm"
            sandbox="allow-same-origin"
            :srcdoc="previewHtml"
          />
          <div v-else class="flex items-center justify-center h-64 text-muted-foreground">
            Нажмите «Обновить предпросмотр», чтобы увидеть письмо
          </div>
        </div>
      </div>
    </div>

    <div class="mt-8 bg-card rounded-lg border border-border overflow-hidden">
      <div class="p-4 border-b border-border flex items-center justify-between">
        <h2 class="text-lg font-semibold text-foreground">История рассылок</h2>
        <button
          type="button"
          class="rounded-md border border-input bg-background px-3 py-1.5 text-sm font-medium text-foreground hover:bg-accent/10 transition-colors disabled:opacity-50"
          :disabled="loadingMailings"
          @click="loadMailings"
        >
          {{ loadingMailings ? 'Загрузка…' : 'Обновить' }}
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-muted/50 text-muted-foreground uppercase">
            <tr>
              <th class="px-4 py-3 font-medium">Email</th>
              <th class="px-4 py-3 font-medium">Отправлено раз</th>
              <th class="px-4 py-3 font-medium">Дата последней</th>
              <th class="px-4 py-3 font-medium">Доступность</th>
              <th class="px-4 py-3 font-medium">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-if="loadingMailings && !mailings.length" class="bg-card">
              <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">Загрузка…</td>
            </tr>
            <tr v-else-if="!mailings.length" class="bg-card">
              <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">Рассылок пока нет</td>
            </tr>
            <tr
              v-for="m in mailings"
              :key="m.email"
              class="bg-card hover:bg-accent/5 transition-colors"
            >
              <td class="px-4 py-3 font-medium text-foreground">{{ m.email }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ m.send_count ?? 0 }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ formatDate(m.last_sent_at) }}</td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                    m.can_send ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                  ]"
                >
                  {{ m.can_send ? 'Можно' : 'Не беспокоить' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <button
                  v-if="m.can_send"
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-md border border-input bg-background px-3 py-1.5 text-sm font-medium text-foreground hover:bg-accent/10 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="resendLoading[m.email]"
                  :title="'Повторить отправку на ' + m.email"
                  @click="resend(m)"
                >
                  <svg v-if="resendLoading[m.email]" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                  {{ resendLoading[m.email] ? 'Отправка…' : 'Повторить' }}
                </button>
                <span v-else class="text-muted-foreground text-xs">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="mailingsPagination && (mailingsPagination.prev_page_url || mailingsPagination.next_page_url)" class="p-4 border-t border-border flex items-center justify-between">
        <p class="text-sm text-muted-foreground">
          Страница {{ mailingsPagination.current_page }} из {{ mailingsPagination.last_page }}
        </p>
        <div class="flex gap-2">
          <button
            type="button"
            class="rounded-md border border-input bg-background px-3 py-1.5 text-sm font-medium text-foreground hover:bg-accent/10 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="!mailingsPagination.prev_page_url"
            @click="goToMailingsPage(mailingsPagination.current_page - 1)"
          >
            Назад
          </button>
          <button
            type="button"
            class="rounded-md border border-input bg-background px-3 py-1.5 text-sm font-medium text-foreground hover:bg-accent/10 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="!mailingsPagination.next_page_url"
            @click="goToMailingsPage(mailingsPagination.current_page + 1)"
          >
            Вперёд
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/axios';

const email = ref('');
const emailError = ref('');
const sending = ref(false);
const message = ref('');
const messageSuccess = ref(false);
const previewHtml = ref('');
const loadingPreview = ref(false);
const previewFrame = ref(null);
const mailings = ref([]);
const loadingMailings = ref(false);
const mailingsPagination = ref(null);
const resendLoading = ref({});

function clearMessage() {
  message.value = '';
  emailError.value = '';
}

function formatDate(value) {
  if (!value) return '—';
  const d = new Date(value);
  return d.toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

async function loadPreview() {
  loadingPreview.value = true;
  clearMessage();
  try {
    const { data } = await apiClient.get('/admin/commercial-proposal/preview');
    previewHtml.value = data.html ?? '';
  } catch (e) {
    message.value = 'Не удалось загрузить предпросмотр.';
    messageSuccess.value = false;
  } finally {
    loadingPreview.value = false;
  }
}

async function loadMailings(page = 1) {
  loadingMailings.value = true;
  try {
    const { data } = await apiClient.get('/admin/commercial-proposal/mailings', {
      params: { page, per_page: 20 },
    });
    mailings.value = data.data ?? [];
    mailingsPagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      prev_page_url: data.prev_page_url,
      next_page_url: data.next_page_url,
    };
  } catch {
    mailings.value = [];
    mailingsPagination.value = null;
  } finally {
    loadingMailings.value = false;
  }
}

function goToMailingsPage(page) {
  if (page < 1) return;
  loadMailings(page);
}

async function send() {
  emailError.value = '';
  if (!email.value?.trim()) {
    emailError.value = 'Укажите email.';
    return;
  }
  sending.value = true;
  clearMessage();
  try {
    await apiClient.post('/admin/commercial-proposal/send', { email: email.value.trim() });
    message.value = 'Коммерческое предложение отправлено на указанный email.';
    messageSuccess.value = true;
    email.value = '';
    loadMailings(1);
  } catch (e) {
    const msg = e.response?.data?.message ?? e.response?.data?.errors?.email?.[0] ?? 'Ошибка отправки.';
    message.value = msg;
    messageSuccess.value = false;
  } finally {
    sending.value = false;
  }
}

async function resend(m) {
  if (!m.can_send) return;
  resendLoading.value = { ...resendLoading.value, [m.email]: true };
  clearMessage();
  try {
    const { data } = await apiClient.post('/admin/commercial-proposal/resend', { email: m.email });
    message.value = data.message ?? 'КП повторно отправлено.';
    messageSuccess.value = true;
    loadMailings(mailingsPagination.value?.current_page ?? 1);
  } catch (e) {
    message.value = e.response?.data?.message ?? 'Ошибка повторной отправки.';
    messageSuccess.value = false;
  } finally {
    resendLoading.value = { ...resendLoading.value, [m.email]: false };
  }
}

onMounted(() => {
  loadPreview();
  loadMailings();
});
</script>
