<script setup>
import { onMounted, reactive, ref } from 'vue'
import api, { parseError } from '../api'
import { auth } from '../auth'
import { fmtDate, statusClass } from '../format'
import { useList } from '../useList'
import Modal from '../components/Modal.vue'
import Pager from '../components/Pager.vue'

const { items, loading, page, lastPage, total, filters, load, go } = useList('/certificates', {
  status: '',
  course_id: '',
  from: '',
  to: '',
})

const participants = ref([])
const courses = ref([])

const showForm = ref(false)
const saving = ref(false)
const form = reactive({ participant_id: '', course_id: '', issued_at: '' })
const errors = ref({})
const generalError = ref('')
const copied = ref(null)

onMounted(async () => {
  const [p, c] = await Promise.all([
    api.get('/participants', { params: { per_page: 100 } }),
    api.get('/courses', { params: { per_page: 100 } }),
  ])
  participants.value = p.data.data
  courses.value = c.data.data
})

function openNew() {
  Object.assign(form, { participant_id: '', course_id: '', issued_at: '' })
  errors.value = {}
  generalError.value = ''
  showForm.value = true
}

async function issue() {
  saving.value = true
  errors.value = {}
  generalError.value = ''
  try {
    const payload = { ...form, issued_at: form.issued_at || null }
    const { data } = await api.post('/certificates', payload)
    showForm.value = false
    await load()
    await downloadPdf(data)
  } catch (e) {
    const { message, fields } = parseError(e)
    errors.value = fields
    generalError.value = Object.keys(fields).length ? '' : message
  } finally {
    saving.value = false
  }
}

async function downloadPdf(cert) {
  try {
    const { data } = await api.get(`/certificates/${cert.id}/pdf`, { responseType: 'blob' })
    const url = URL.createObjectURL(data)
    const a = document.createElement('a')
    a.href = url
    a.download = `certificate-${cert.code}.pdf`
    a.click()
    URL.revokeObjectURL(url)
  } catch (e) {
    alert('Could not generate the PDF.')
  }
}

async function copyLink(cert) {
  const link = `${window.location.origin}/validate/${cert.code}`
  try {
    await navigator.clipboard.writeText(link)
    copied.value = cert.id
    setTimeout(() => (copied.value = null), 1500)
  } catch {
    prompt('Validation link:', link)
  }
}

async function revoke(cert) {
  if (!confirm(`Revoke certificate ${cert.code}? It will be reported as invalid on the public page.`)) return
  try {
    await api.post(`/certificates/${cert.id}/revoke`)
    await load()
  } catch (e) {
    alert(parseError(e).message)
  }
}

async function remove(cert) {
  if (!confirm(`Delete certificate ${cert.code}? This cannot be undone.`)) return
  try {
    await api.delete(`/certificates/${cert.id}`)
    await load()
  } catch (e) {
    alert(parseError(e).message)
  }
}
</script>

<template>
  <div>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-2xl font-bold">Certificates</h1>
      <button class="btn btn-primary" @click="openNew">+ Issue certificate</button>
    </div>

    <div class="card !p-0">
      <div class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-2 lg:grid-cols-5">
        <input v-model="filters.search" class="input" type="search" placeholder="Search code, name or course..." />
        <select v-model="filters.status" class="input" aria-label="Status">
          <option value="">All statuses</option>
          <option value="valid">Valid</option>
          <option value="revoked">Revoked</option>
        </select>
        <select v-model="filters.course_id" class="input" aria-label="Course">
          <option value="">All courses</option>
          <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <input v-model="filters.from" class="input" type="date" aria-label="From date" />
        <input v-model="filters.to" class="input" type="date" aria-label="To date" />
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="th">Code</th>
              <th class="th">Participant</th>
              <th class="th">Course</th>
              <th class="th">Hours</th>
              <th class="th">Issued</th>
              <th class="th">Status</th>
              <th class="th text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading && !items.length"><td class="td text-slate-400" colspan="7">Loading...</td></tr>
            <tr v-else-if="!items.length"><td class="td text-slate-400" colspan="7">No certificates found.</td></tr>
            <tr v-for="c in items" :key="c.id">
              <td class="td font-mono text-xs">{{ c.code }}</td>
              <td class="td">{{ c.participant_name }}</td>
              <td class="td">{{ c.course_name }}</td>
              <td class="td">{{ c.workload_hours }}h</td>
              <td class="td whitespace-nowrap">{{ fmtDate(c.issued_at) }}</td>
              <td class="td"><span class="badge" :class="statusClass[c.status]">{{ c.status }}</span></td>
              <td class="td whitespace-nowrap text-right">
                <button class="btn btn-ghost btn-sm" @click="downloadPdf(c)">PDF</button>
                <button class="btn btn-ghost btn-sm ml-1" @click="copyLink(c)">{{ copied === c.id ? 'Copied!' : 'Link' }}</button>
                <template v-if="auth.isAdmin">
                  <button v-if="c.status === 'valid'" class="btn btn-ghost btn-sm ml-1" @click="revoke(c)">Revoke</button>
                  <button class="btn btn-danger btn-sm ml-1" @click="remove(c)">Delete</button>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pager :page="page" :last-page="lastPage" :total="total" @go="go" />
    </div>

    <Modal :open="showForm" title="Issue certificate" @close="showForm = false">
      <form class="space-y-4" @submit.prevent="issue">
        <div>
          <label class="label" for="participant">Participant <span class="text-red-500">*</span></label>
          <select id="participant" v-model="form.participant_id" class="input" required>
            <option value="" disabled>Select a participant</option>
            <option v-for="p in participants" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <p v-if="errors.participant_id" class="err">{{ errors.participant_id[0] }}</p>
        </div>
        <div>
          <label class="label" for="course">Course / event <span class="text-red-500">*</span></label>
          <select id="course" v-model="form.course_id" class="input" required>
            <option value="" disabled>Select a course</option>
            <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.name }} ({{ c.workload_hours }}h)</option>
          </select>
          <p v-if="errors.course_id" class="err">{{ errors.course_id[0] }}</p>
        </div>
        <div>
          <label class="label" for="issued">Issue date</label>
          <input id="issued" v-model="form.issued_at" class="input" type="date" />
          <p class="mt-1 text-xs text-slate-500">Leave empty to use today.</p>
        </div>
        <p v-if="generalError" class="err">{{ generalError }}</p>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="btn btn-ghost" @click="showForm = false">Cancel</button>
          <button class="btn btn-primary" :disabled="saving">{{ saving ? 'Issuing...' : 'Issue & download PDF' }}</button>
        </div>
      </form>
    </Modal>
  </div>
</template>
