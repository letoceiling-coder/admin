<template>
  <aside
    class="relative flex flex-col w-72 bg-sidebar-background text-sidebar-foreground border-r border-sidebar-border shrink-0 lg:flex hidden"
  >
    <div class="flex h-16 items-center border-b border-sidebar-border justify-between px-6">
      <router-link to="/crm" class="text-xl font-bold text-sidebar-foreground hover:opacity-90">CRM</router-link>
    </div>
    <nav class="flex-1 overflow-y-auto space-y-1 p-4">
      <!-- Пункты меню добавляются по плану реализации -->
    </nav>
    <div class="border-t border-sidebar-border space-y-3 p-4">
      <div class="flex items-center gap-3 px-2">
        <div class="h-10 w-10 rounded-full bg-primary text-primary-foreground border-2 border-primary/80 flex items-center justify-center text-sm font-bold shrink-0 shadow-sm">
          {{ userInitials }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-sidebar-foreground">{{ user?.name || 'Пользователь' }}</p>
          <p class="text-xs text-muted-foreground truncate">{{ user?.email || '—' }}</p>
        </div>
      </div>
      <div class="flex gap-2">
        <router-link
          to="/admin"
          class="flex-1 flex justify-center items-center gap-2 px-3 py-2 text-muted-foreground hover:text-foreground rounded-md hover:bg-accent/10 transition-colors text-sm"
        >
          <span>Админка</span>
        </router-link>
        <button
          type="button"
          class="flex justify-start gap-2 px-4 py-2 text-muted-foreground hover:text-foreground rounded-md hover:bg-accent/10 transition-colors"
          @click="handleLogout"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          <span>Выйти</span>
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useUserInitials } from '@/composables/useUserInitials';

const router = useRouter();
const auth = useAuthStore();
const user = computed(() => auth.user);
const userInitials = useUserInitials(user);

async function handleLogout() {
  await auth.logout();
  router.push({ name: 'admin.login', query: { redirect: '/crm' } });
}
</script>
