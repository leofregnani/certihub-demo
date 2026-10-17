<script setup>
defineProps({ page: Number, lastPage: Number, total: Number })
defineEmits(['go'])
</script>

<template>
  <div class="flex items-center justify-between px-4 py-3 text-sm text-slate-600">
    <span>{{ total }} result{{ total === 1 ? '' : 's' }}</span>
    <div class="flex items-center gap-2">
      <button class="btn btn-ghost btn-sm" :disabled="page <= 1" @click="$emit('go', page - 1)">Previous</button>
      <span>{{ page }} / {{ lastPage }}</span>
      <button class="btn btn-ghost btn-sm" :disabled="page >= lastPage" @click="$emit('go', page + 1)">Next</button>
    </div>
  </div>
</template>
