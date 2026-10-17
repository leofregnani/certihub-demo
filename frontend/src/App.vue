<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from './components/AppLayout.vue'

const route = useRoute()
const isPublic = computed(() => route.meta.public)
</script>

<template>
  <RouterView v-if="isPublic" />
  <AppLayout v-else>
    <RouterView />
  </AppLayout>
</template>
