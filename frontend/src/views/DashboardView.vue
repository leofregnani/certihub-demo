<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../api'
import { fmtDate, statusClass } from '../format'

const data = ref(null)

onMounted(async () => {
  data.value = (await api.get('/dashboard')).data
})

const cards = computed(() => {
  const t = data.value?.totals
  if (!t) return []
  return [
    { label: 'Participants', value: t.participants },
    { label: 'Courses & events', value: t.courses },
    { label: 'Certificates issued', value: t.certificates },
    { label: 'Public validations', value: t.validations },
  ]
})

const max = computed(() => Math.max(1, ...(data.value?.per_month.map((m) => m.total) || [1])))
</script>

<template>
  <div>
    <h1 class="mb-4 text-2xl font-bold">Dashboard</h1>

    <p v-if="!data" class="text-slate-400">Loading...</p>

    <template v-else>
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div v-for="c in cards" :key="c.label" class="card">
          <div class="text-3xl font-bold">{{ c.value }}</div>
          <div class="text-sm text-slate-500">{{ c.label }}</div>
        </div>
      </div>

      <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card">
          <h2 class="mb-4 font-semibold">Certificates per month</h2>
          <div class="flex h-48 gap-3">
            <div v-for="m in data.per_month" :key="m.month" class="flex h-full flex-1 flex-col items-center gap-1">
              <span class="text-xs text-slate-500">{{ m.total }}</span>
              <div class="flex w-full flex-1 items-end">
                <div class="w-full rounded-t bg-blue-500" :style="{ height: (m.total / max) * 100 + '%', minHeight: '2px' }" />
              </div>
              <span class="text-xs text-slate-500">{{ m.label }}</span>
            </div>
          </div>
        </div>

        <div class="card">
          <h2 class="mb-4 font-semibold">Latest certificates</h2>
          <ul class="divide-y divide-slate-100">
            <li v-for="c in data.latest" :key="c.id" class="flex items-center justify-between gap-3 py-2 text-sm">
              <div class="min-w-0">
                <div class="truncate font-medium">{{ c.participant_name }}</div>
                <div class="truncate text-xs text-slate-500">{{ c.course_name }} · {{ fmtDate(c.issued_at) }}</div>
              </div>
              <span class="badge" :class="statusClass[c.status]">{{ c.status }}</span>
            </li>
            <li v-if="!data.latest.length" class="py-2 text-sm text-slate-400">No certificates yet.</li>
          </ul>
        </div>
      </div>
    </template>
  </div>
</template>
