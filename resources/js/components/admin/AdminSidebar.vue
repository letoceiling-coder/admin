<template>
  <aside
    class="relative flex flex-col w-72 bg-sidebar-background text-sidebar-foreground border-r border-sidebar-border shrink-0 lg:flex hidden"
  >
    <div class="flex h-16 items-center border-b border-sidebar-border justify-between px-6">
      <h1 class="text-xl font-bold text-sidebar-foreground">CMS Admin</h1>
    </div>
    <nav class="flex-1 overflow-y-auto space-y-1 p-4">
      <router-link
        to="/admin"
        active-class="router-link-active bg-sidebar-accent text-sidebar-accent-foreground"
        class="nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground px-4 py-3 gap-3"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
        </span>
        <span>Главная</span>
      </router-link>
      <router-link
        to="/admin/subscribers"
        :class="[
          'nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3',
          $route.path.startsWith('/admin/subscribers') && !$route.path.startsWith('/admin/subscription-applications')
            ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground'
            : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
        ]"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </span>
        <span>Подписчики</span>
      </router-link>
      <router-link
        to="/admin/subscription-applications"
        :class="[
          'nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3',
          $route.path.startsWith('/admin/subscription-applications')
            ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground'
            : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
        ]"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
          </svg>
        </span>
        <span>Заявки</span>
      </router-link>
      <router-link
        to="/admin/plans"
        :class="[
          'nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3',
          $route.path.startsWith('/admin/plans')
            ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground'
            : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
        ]"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </span>
        <span>Управление</span>
      </router-link>
      <div class="rounded-xl overflow-hidden">
        <div
          class="flex items-center rounded-xl text-sm font-medium text-sidebar-foreground/80 px-4 py-3 gap-3 cursor-default"
        >
          <span class="h-5 w-5 shrink-0">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
          </span>
          <span>Настройки</span>
        </div>
      </div>
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
      <button
        type="button"
        class="w-full flex justify-start gap-2 px-4 py-2 text-muted-foreground hover:text-foreground rounded-md hover:bg-accent/10 transition-colors"
        @click="handleLogout"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
        </svg>
        <span>Выйти</span>
      </button>
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
  router.push({ name: 'admin.login' });
}
</script>

<style scoped>
.router-link-active {
  background-color: hsl(var(--sidebar-accent));
  color: hsl(var(--sidebar-accent-foreground));
}
</style>
