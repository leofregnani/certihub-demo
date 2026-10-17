<script setup>
/**
 * Generic list + create/edit modal + delete, used by Participants and Courses.
 * `fields`: [{ key, label, type: 'text'|'email'|'number'|'date'|'textarea', required }]
 * `columns`: [{ key, label, format? }]
 */
import { reactive, ref } from 'vue'
import api, { parseError } from '../api'
import { auth } from '../auth'
import { useList } from '../useList'
import Modal from './Modal.vue'
import Pager from './Pager.vue'

const props = defineProps({
  title: String,
  noun: String,
  url: String,
  columns: Array,
  fields: Array,
})

const { items, loading, page, lastPage, total, filters, load, go } = useList(props.url)

const showForm = ref(false)
const saving = ref(false)
const editingId = ref(null)
const form = reactive({})
const errors = ref({})
const generalError = ref('')

function blank() {
  for (const key of Object.keys(form)) delete form[key]
  for (const f of props.fields) form[f.key] = ''
}

function openNew() {
  blank()
  editingId.value = null
  errors.value = {}
  generalError.value = ''
  showForm.value = true
}

function openEdit(row) {
  blank()
  for (const f of props.fields) form[f.key] = row[f.key] ?? ''
  editingId.value = row.id
  errors.value = {}
  generalError.value = ''
  showForm.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  generalError.value = ''
  try {
    const payload = { ...form }
    for (const f of props.fields) if (payload[f.key] === '') payload[f.key] = null
    if (editingId.value) await api.put(`${props.url}/${editingId.value}`, payload)
    else await api.post(props.url, payload)
    showForm.value = false
    await load()
  } catch (e) {
    const { message, fields } = parseError(e)
    errors.value = fields
    generalError.value = Object.keys(fields).length ? '' : message
  } finally {
    saving.value = false
  }
}

async function remove(row) {
  if (!confirm(`Delete this ${props.noun}? This cannot be undone.`)) return
  try {
    await api.delete(`${props.url}/${row.id}`)
    await load()
  } catch (e) {
    alert(parseError(e).message)
  }
}

function cell(col, row) {
  const value = row[col.key]
  if (col.format) return col.format(value)
  return value === null || value === '' || value === undefined ? '—' : value
}
</script>

<template>
  <div>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-2xl font-bold">{{ title }}</h1>
      <button class="btn btn-primary" @click="openNew">+ New {{ noun }}</button>
    </div>

    <div class="card !p-0">
      <div class="border-b border-slate-200 p-4">
        <input v-model="filters.search" class="input sm:max-w-xs" type="search" :placeholder="`Search ${title.toLowerCase()}...`" />
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th v-for="col in columns" :key="col.key" class="th">{{ col.label }}</th>
              <th class="th text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading && !items.length">
              <td class="td text-slate-400" :colspan="columns.length + 1">Loading...</td>
            </tr>
            <tr v-else-if="!items.length">
              <td class="td text-slate-400" :colspan="columns.length + 1">Nothing found.</td>
            </tr>
            <tr v-for="row in items" :key="row.id">
              <td v-for="col in columns" :key="col.key" class="td">{{ cell(col, row) }}</td>
              <td class="td whitespace-nowrap text-right">
                <button class="btn btn-ghost btn-sm" @click="openEdit(row)">Edit</button>
                <button v-if="auth.isAdmin" class="btn btn-danger btn-sm ml-1" @click="remove(row)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pager :page="page" :last-page="lastPage" :total="total" @go="go" />
    </div>

    <Modal :open="showForm" :title="editingId ? `Edit ${noun}` : `New ${noun}`" @close="showForm = false">
      <form class="space-y-4" @submit.prevent="save">
        <div v-for="f in fields" :key="f.key">
          <label class="label" :for="`f-${f.key}`">{{ f.label }}<span v-if="f.required" class="text-red-500"> *</span></label>
          <textarea v-if="f.type === 'textarea'" :id="`f-${f.key}`" v-model="form[f.key]" class="input" rows="3" />
          <input
            v-else
            :id="`f-${f.key}`"
            v-model="form[f.key]"
            class="input"
            :type="f.type || 'text'"
            :required="f.required"
            :min="f.type === 'number' ? 1 : undefined"
          />
          <p v-if="errors[f.key]" class="err">{{ errors[f.key][0] }}</p>
        </div>
        <p v-if="generalError" class="err">{{ generalError }}</p>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="btn btn-ghost" @click="showForm = false">Cancel</button>
          <button class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
        </div>
      </form>
    </Modal>
  </div>
</template>
