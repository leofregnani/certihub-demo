<script setup>
import { ref } from 'vue'
import api, { parseError } from '../api'

const email = ref('')
const sent = ref(false)
const error = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await api.post('/auth/forgot-password', { email: email.value })
    sent.value = true
  } catch (e) {
    error.value = parseError(e).message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center p-4">
    <form class="card w-full max-w-sm space-y-4" @submit.prevent="submit">
      <h1 class="text-center text-xl font-bold">Reset your password</h1>

      <p v-if="sent" class="rounded bg-green-50 p-3 text-sm text-green-700">
        If that e-mail exists, a reset link is on its way. In the demo the mail driver is "log", so the link is written to
        <code>backend/storage/logs/laravel.log</code>.
      </p>

      <template v-else>
        <div>
          <label class="label" for="email">E-mail</label>
          <input id="email" v-model="email" class="input" type="email" required />
        </div>
        <p v-if="error" class="err">{{ error }}</p>
        <button class="btn btn-primary w-full" :disabled="loading">{{ loading ? 'Sending...' : 'Send reset link' }}</button>
      </template>

      <RouterLink to="/login" class="block text-center text-sm text-blue-600 hover:underline">Back to sign in</RouterLink>
    </form>
  </div>
</template>
