<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '../auth'
import { parseError } from '../api'

const router = useRouter()
const email = ref(import.meta.env.DEV ? 'admin@example.com' : '')
const password = ref(import.meta.env.DEV ? 'password' : '')
const error = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await auth.login(email.value, password.value)
    router.push('/')
  } catch (e) {
    const { message, fields } = parseError(e)
    error.value = fields.email?.[0] || message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center p-4">
    <form class="card w-full max-w-sm space-y-4" @submit.prevent="submit">
      <div class="text-center">
        <h1 class="text-2xl font-bold">CertiHub Demo</h1>
        <p class="text-sm text-slate-500">Sign in to manage certificates</p>
      </div>

      <div>
        <label class="label" for="email">E-mail</label>
        <input id="email" v-model="email" class="input" type="email" autocomplete="username" required />
      </div>
      <div>
        <label class="label" for="password">Password</label>
        <input id="password" v-model="password" class="input" type="password" autocomplete="current-password" required />
      </div>

      <p v-if="error" class="err">{{ error }}</p>
      <button class="btn btn-primary w-full" :disabled="loading">{{ loading ? 'Signing in...' : 'Sign in' }}</button>

      <div class="flex justify-between text-sm">
        <RouterLink to="/forgot-password" class="text-blue-600 hover:underline">Forgot password?</RouterLink>
        <RouterLink to="/validate" class="text-blue-600 hover:underline">Validate a certificate</RouterLink>
      </div>

      <p v-if="$route.query.reset" class="rounded bg-green-50 p-2 text-center text-xs text-green-700">
        Password updated. You can sign in now.
      </p>
      <p class="rounded bg-slate-50 p-2 text-center text-xs text-slate-500">
        Demo accounts: admin@example.com or user@example.com — password: <code>password</code>
      </p>
    </form>
  </div>
</template>
