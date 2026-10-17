<script setup>
defineProps({ open: Boolean, title: String })
defineEmits(['close'])
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-0 sm:items-center sm:p-4" @click.self="$emit('close')">
    <div class="max-h-[90vh] w-full overflow-y-auto rounded-t-2xl bg-white p-6 shadow-xl sm:max-w-lg sm:rounded-2xl">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-bold">{{ title }}</h2>
        <button class="text-2xl leading-none text-slate-400 hover:text-slate-600" aria-label="Close" @click="$emit('close')">×</button>
      </div>
      <slot />
    </div>
  </div>
</template>
