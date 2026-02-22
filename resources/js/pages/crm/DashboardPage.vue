<template>
  <div class="crm-dashboard-page">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">CRM</h1>
      <p class="text-muted-foreground mt-1">Панель управления контентом и интеграциями</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-card rounded-lg border border-border p-6">
        <h2 class="text-lg font-semibold mb-2">Top services (last 30 days)</h2>
        <p class="text-muted-foreground text-sm mb-2">По заявкам из бота (lead_created с source_service_id)</p>
        <ul v-if="topServices.length" class="space-y-1 text-sm">
          <li v-for="s in topServices" :key="s.service_id" class="flex justify-between gap-2">
            <span class="truncate">{{ s.name }}</span>
            <span class="text-muted-foreground shrink-0">{{ s.count }}</span>
          </li>
        </ul>
        <p v-else class="text-muted-foreground text-sm">Нет данных</p>
      </div>
      <div class="bg-card rounded-lg border border-border p-6">
        <h2 class="text-lg font-semibold mb-2">Top cases (last 30 days)</h2>
        <p class="text-muted-foreground text-sm mb-2">По заявкам из бота (lead_created с source_case_id)</p>
        <ul v-if="topCases.length" class="space-y-1 text-sm">
          <li v-for="c in topCases" :key="c.case_id" class="flex justify-between gap-2">
            <span class="truncate">{{ c.title }}</span>
            <span class="text-muted-foreground shrink-0">{{ c.count }}</span>
          </li>
        </ul>
        <p v-else class="text-muted-foreground text-sm">Нет данных</p>
      </div>
      <div class="bg-card rounded-lg border border-border p-6">
        <h2 class="text-lg font-semibold mb-2">Раздел</h2>
        <p class="text-muted-foreground">Контент будет добавлен по плану реализации</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/axios';

const topServices = ref([]);
const topCases = ref([]);

async function loadAnalytics() {
  try {
    const { data } = await apiClient.get('/crm/telegram/analytics/top', { params: { days: 30 } });
    topServices.value = data.data?.top_services ?? [];
    topCases.value = data.data?.top_cases ?? [];
  } catch {
    topServices.value = [];
    topCases.value = [];
  }
}

onMounted(loadAnalytics);
</script>
