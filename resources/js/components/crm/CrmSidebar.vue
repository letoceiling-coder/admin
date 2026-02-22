<template>
  <aside
    class="relative flex flex-col w-72 bg-sidebar-background text-sidebar-foreground border-r border-sidebar-border shrink-0 lg:flex hidden"
  >
    <div class="flex h-16 items-center border-b border-sidebar-border justify-between px-6">
      <router-link to="/crm" class="text-xl font-bold text-sidebar-foreground hover:opacity-90">CRM</router-link>
    </div>
    <nav class="flex-1 overflow-y-auto space-y-1 p-4">
      <router-link
        to="/crm"
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
        to="/crm/neeklo-bot/settings"
        :class="['nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3', $route.path === '/crm/neeklo-bot/settings' ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground']"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </span>
        <span>Настройки бота</span>
      </router-link>
      <router-link
        to="/crm/neeklo-bot/services"
        :class="['nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3', $route.path.startsWith('/crm/neeklo-bot/services') && !$route.path.includes('categories') ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground']"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
        </span>
        <span>Услуги</span>
      </router-link>
      <router-link
        to="/crm/neeklo-bot/services/categories"
        :class="['nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3', $route.path.includes('categories') ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground']"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
          </svg>
        </span>
        <span>Категории услуг</span>
      </router-link>
      <router-link
        to="/crm/neeklo-bot/cases"
        :class="['nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3', $route.path.startsWith('/crm/neeklo-bot/cases') ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground']"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
        </span>
        <span>Кейсы</span>
      </router-link>
      <router-link
        to="/crm/neeklo-bot/reviews"
        :class="['nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3', $route.path.startsWith('/crm/neeklo-bot/reviews') ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground']"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
          </svg>
        </span>
        <span>Отзывы</span>
      </router-link>
      <router-link
        to="/crm/neeklo-bot/faq"
        :class="['nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3', $route.path.startsWith('/crm/neeklo-bot/faq') ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground']"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </span>
        <span>FAQ</span>
      </router-link>
      <router-link
        to="/crm/neeklo-bot/leads"
        :class="['nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3', $route.path.startsWith('/crm/neeklo-bot/leads') ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground']"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </span>
        <span>Лиды</span>
      </router-link>
      <router-link
        to="/crm/neeklo-bot/tickets"
        :class="['nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3', $route.path.startsWith('/crm/neeklo-bot/tickets') ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground']"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
          </svg>
        </span>
        <span>Тикеты</span>
      </router-link>
      <router-link
        to="/crm/documents"
        :class="['nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all px-4 py-3 gap-3', $route.path.startsWith('/crm/documents') ? 'router-link-active bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground']"
      >
        <span class="h-5 w-5 shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
        </span>
        <span>Документация</span>
      </router-link>
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

<style scoped>
.router-link-active {
  background-color: hsl(var(--sidebar-accent));
  color: hsl(var(--sidebar-accent-foreground));
}
</style>
