<template>
  <div class="flex h-screen w-full overflow-hidden bg-background">
    <CrmSidebar />
    <div class="flex flex-1 flex-col overflow-hidden min-w-0">
      <CrmHeader />
      <main class="flex-1 overflow-y-auto bg-background p-4 sm:p-6 lg:p-8">
        <div class="max-w-[1600px] mx-auto">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import CrmSidebar from '@/components/crm/CrmSidebar.vue';
import CrmHeader from '@/components/crm/CrmHeader.vue';
</script>
