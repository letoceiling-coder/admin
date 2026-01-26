<template>
  <div ref="rootRef" class="relative">
    <button
      type="button"
      class="relative h-11 w-11 flex items-center justify-center rounded-md hover:bg-accent/10 transition-colors"
      aria-label="Уведомления"
      aria-expanded="isOpen"
      aria-haspopup="true"
      @click="toggle"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
      </svg>
      <span
        v-if="notificationsStore.unreadCount > 0"
        class="absolute -top-1 -right-1 min-w-[1.25rem] h-5 px-1 flex items-center justify-center bg-red-500 text-white text-xs font-bold rounded-full"
      >
        {{ notificationsStore.unreadCount > 9 ? '9+' : notificationsStore.unreadCount }}
      </span>
    </button>
    <div
      v-show="isOpen"
      class="absolute right-0 top-full mt-2 w-80 max-h-96 overflow-hidden flex flex-col bg-card border border-border rounded-lg shadow-lg z-40"
      role="menu"
    >
      <div class="flex items-center justify-between shrink-0 p-4 border-b border-border">
        <h3 class="font-semibold text-foreground">Уведомления</h3>
      </div>
      <div class="overflow-y-auto flex-1 min-h-0">
        <div
          v-if="notificationsStore.loading"
          class="p-4 text-center text-muted-foreground text-sm"
        >
          Загрузка…
        </div>
        <div
          v-else-if="notificationsStore.list.length === 0"
          class="p-4 text-center text-muted-foreground text-sm"
        >
          Нет уведомлений
        </div>
        <div v-else class="divide-y divide-border">
          <button
            v-for="n in notificationsStore.list"
            :key="n.id"
            type="button"
            class="w-full text-left p-4 hover:bg-accent/10 transition-colors"
            :class="{ 'bg-accent/5': !n.read }"
            role="menuitem"
            @click="onItemClick(n)"
          >
            <div class="flex items-start gap-2">
              <span
                v-if="!n.read"
                class="shrink-0 mt-1.5 w-2 h-2 rounded-full bg-primary"
                aria-hidden="true"
              />
              <div class="flex-1 min-w-0">
                <p class="font-medium text-sm text-foreground truncate">{{ n.title || 'Уведомление' }}</p>
                <p class="text-sm text-muted-foreground mt-0.5 line-clamp-2">{{ n.message || '' }}</p>
                <p class="text-xs text-muted-foreground mt-1">{{ formatDate(n.created_at) }}</p>
              </div>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationsStore } from '@/stores/notifications';

const router = useRouter();
const notificationsStore = useNotificationsStore();
const rootRef = ref(null);
const isOpen = ref(false);

function toggle() {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    notificationsStore.fetchNotifications().catch(() => {});
  }
}

async function onItemClick(notification) {
  if (!notification.read) {
    try {
      await notificationsStore.markAsRead(notification.id);
    } catch {}
  }
  isOpen.value = false;
  if (notification.application_id) {
    router.push({ name: 'admin.subscription-applications' });
  }
}

function formatDate(value) {
  if (!value) return '';
  const d = new Date(value);
  const now = new Date();
  const diff = now - d;
  const m = Math.floor(diff / 60000);
  const h = Math.floor(diff / 3600000);
  const day = Math.floor(diff / 86400000);
  if (m < 1) return 'Только что';
  if (m < 60) return `${m} мин. назад`;
  if (h < 24) return `${h} ч. назад`;
  if (day < 7) return `${day} дн. назад`;
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: d.getFullYear() !== now.getFullYear() ? 'numeric' : undefined });
}

function handleClickOutside(e) {
  if (!rootRef.value?.contains(e.target)) {
    isOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
