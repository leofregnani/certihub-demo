<script setup>
import { reactive, ref } from 'vue'
import api, { parseError } from '../api'
import { auth } from '../auth'
import { useList } from '../useList'
import Modal from '../components/Modal.vue'
import Pager from '../components/Pager.vue'

const { items, loading, page, lastPage, total, filters, load, go } = useList('/users')

const showForm = ref(false)
const saving = ref(false)
const editingId = ref(null)
const form = reactive({ name: '', email: '', password: '', role: 'user', active: true })
const errors = ref({})
const generalError = ref('')

function openNew() {
  Object.assign(form, { name: '', email: '', password: '', role: 'user', active: true })
  editingId.value = null
  errors.value = {}
  generalError.value = ''
  showForm.value = true
}

function openEdit(user) {
  Object.assign(form, { name: user.name, email: user.email, password: '', role: user.role, active: !!user.active })
  editingId.value = user.id
  errors.value = {}
  generalError.value = ''
  showForm.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  generalError.value = ''
  try {
    if (editingId.value) await api.put(`/users/${editingId.value}`, form)
    else await api.post('/users', form)
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

async function remove(user) {
  if (!confirm(`Delete user ${user.email}?`)) return
  try {
    await api.delete(`/users/${user.id}`)
    await load()
  } catch (e) {
    const { message, fields } = parseError(e)
    alert(Object.values(fields)[0]?.[0] || message)
  }
}
</script>

<template>
  <div>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-2xl font-bold">Users</h1>
      <button class="btn btn-primary" @click="openNew">+ New user</button>
    </div>

    <div class="card !p-0">
      <div class="border-b border-slate-200 p-4">
        <input v-model="filters.search" class="input sm:max-w-xs" type="search" placeholder="Search users..." />
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="th">Name</th>
              <th class="th">E-mail</th>
              <th class="th">Role</th>
              <th class="th">Status</th>
              <th class="th text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading && !items.length"><td class="td text-slate-400" colspan="5">Loading...</td></tr>
            <tr v-for="u in items" :key="u.id">
              <td class="td">{{ u.name }}</td>
              <td class="td">{{ u.email }}</td>
              <td class="td capitalize">{{ u.role }}</td>
              <td class="td">
                <span class="badge" :class="u.active ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-600'">
                  {{ u.active ? 'active' : 'inactive' }}
                </span>
              </td>
              <td class="td whitespace-nowrap text-right">
                <button class="btn btn-ghost btn-sm" @click="openEdit(u)">Edit</button>
                <button v-if="u.id !== auth.state.user?.id" class="btn btn-danger btn-sm ml-1" @click="remove(u)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pager :page="page" :last-page="lastPage" :total="total" @go="go" />
    </div>

    <Modal :open="showForm" :title="editingId ? 'Edit user' : 'New user'" @close="showForm = false">
      <form class="space-y-4" @submit.prevent="save">
        <div>
          <label class="label" for="u-name">Name <span class="text-red-500">*</span></label>
          <input id="u-name" v-model="form.name" class="input" required />
          <p v-if="errors.name" class="err">{{ errors.name[0] }}</p>
        </div>
        <div>
          <label class="label" for="u-email">E-mail <span class="text-red-500">*</span></label>
          <input id="u-email" v-model="form.email" class="input" type="email" required />
          <p v-if="errors.email" class="err">{{ errors.email[0] }}</p>
        </div>
        <div>
          <label class="label" for="u-pass">Password <span v-if="!editingId" class="text-red-500">*</span></label>
          <input id="u-pass" v-model="form.password" class="input" type="password" minlength="8" autocomplete="new-password" :required="!editingId" />
          <p v-if="editingId" class="mt-1 text-xs text-slate-500">Leave empty to keep the current password.</p>
          <p v-if="errors.password" class="err">{{ errors.password[0] }}</p>
        </div>
        <div>
          <label class="label" for="u-role">Role</label>
          <select id="u-role" v-model="form.role" class="input">
            <option value="user">User</option>
            <option value="admin">Administrator</option>
          </select>
          <p v-if="errors.role" class="err">{{ errors.role[0] }}</p>
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.active" type="checkbox" class="h-4 w-4 rounded border-slate-300" />
          Account is active
        </label>
        <p v-if="generalError" class="err">{{ generalError }}</p>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="btn btn-ghost" @click="showForm = false">Cancel</button>
          <button class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
        </div>
      </form>
    </Modal>
  </div>
</template>
