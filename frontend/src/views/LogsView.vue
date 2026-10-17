<script setup>
import { fmtDateTime } from '../format'
import { useList } from '../useList'
import Pager from '../components/Pager.vue'

const { items, loading, page, lastPage, total, filters, go } = useList('/logs', { action: '', from: '', to: '' })

const groups = [
  { value: '', label: 'All actions' },
  { value: 'auth.', label: 'Login / logout' },
  { value: 'certificate.issued', label: 'Certificates issued' },
  { value: 'certificate.validated', label: 'Validations' },
  { value: 'certificate.', label: 'All certificate actions' },
  { value: 'participant.', label: 'Participants' },
  { value: 'course.', label: 'Courses' },
  { value: 'user.', label: 'Users' },
]

function tone(action) {
  if (action.endsWith('deleted') || action.endsWith('revoked') || action.endsWith('failed')) return 'bg-red-100 text-red-800'
  if (action.endsWith('created') || action.endsWith('issued')) return 'bg-green-100 text-green-800'
  if (action.endsWith('updated')) return 'bg-amber-100 text-amber-800'
  return 'bg-slate-200 text-slate-700'
}
</script>

<template>
  <div>
    <h1 class="mb-4 text-2xl font-bold">Audit logs</h1>

    <div class="card !p-0">
      <div class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input v-model="filters.search" class="input" type="search" placeholder="Search description or IP..." />
        <select v-model="filters.action" class="input" aria-label="Action">
          <option v-for="g in groups" :key="g.value" :value="g.value">{{ g.label }}</option>
        </select>
        <input v-model="filters.from" class="input" type="date" aria-label="From date" />
        <input v-model="filters.to" class="input" type="date" aria-label="To date" />
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="th">When</th>
              <th class="th">User</th>
              <th class="th">Action</th>
              <th class="th">Description</th>
              <th class="th">IP</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading && !items.length"><td class="td text-slate-400" colspan="5">Loading...</td></tr>
            <tr v-else-if="!items.length"><td class="td text-slate-400" colspan="5">No log entries found.</td></tr>
            <tr v-for="l in items" :key="l.id">
              <td class="td whitespace-nowrap">{{ fmtDateTime(l.created_at) }}</td>
              <td class="td">{{ l.user?.name || 'Anonymous' }}</td>
              <td class="td"><span class="badge" :class="tone(l.action)">{{ l.action }}</span></td>
              <td class="td">{{ l.description }}</td>
              <td class="td font-mono text-xs">{{ l.ip_address }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pager :page="page" :last-page="lastPage" :total="total" @go="go" />
    </div>
  </div>
</template>
