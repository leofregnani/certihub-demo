<script setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api, { parseError } from '../api'

const route = useRoute()
const router = useRouter()

const form = reactive({
  token: route.query.token || '',
  email: route.query.email || '',
  password: '',
  password_confirmation: '',
})
const errors = ref({})
const error = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  errors.value = {}
  error.value = ''
  try {
    await api.post('/auth/reset-password', form)
    router.push({ path: '/login', query: { reset: '1' } })
  } catch (e) {
    const { message, fields } = parseError(e)
    errors.value = fields
    error.value = Object.keys(fields).length ? '' : message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center p-4">
    <form class="card w-full max-w-sm space-y-4" @submit.prevent="submit">
      <h1 class="text-center text-xl font-bold">Choose a new password</h1>

      <div>
        <label class="label" for="email">E-mail</label>
        <input id="email" v-model="form.email" class="input" type="email" required />
        <p v-if="errors.email" class="err">{{ errors.email[0] }}</p>
      </div>
      <div>
        <label class="label" for="password">New password</label>
        <input id="password" v-model="form.password" class="input" type="password" minlength="8" autocomplete="new-password" required />
        <p v-if="errors.password" class="err">{{ errors.password[0] }}</p>
      </div>
      <div>
        <label class="label" for="confirm">Confirm password</label>
        <input id="confirm" v-model="form.password_confirmation" class="input" type="password" autocomplete="new-password" required />
      </div>

      <p v-if="error" class="err">{{ error }}</p>
      <button class="btn btn-primary w-full" :disabled="loading">{{ loading ? 'Saving...' : 'Update password' }}</button>
    </form>
  </div>
</template>
