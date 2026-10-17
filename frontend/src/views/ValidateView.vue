<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'
import { fmtDate } from '../format'

const route = useRoute()
const router = useRouter()

const code = ref(route.params.code || '')
const result = ref(null)
const loading = ref(false)
const failed = ref(false)

async function check() {
  const value = code.value.trim()
  if (!value) return
  loading.value = true
  failed.value = false
  result.value = null
  router.replace(`/validate/${encodeURIComponent(value)}`)
  try {
    const { data } = await api.get(`/public/certificates/${encodeURIComponent(value)}`)
    result.value = data
  } catch (e) {
    if (e.response?.status === 404) result.value = e.response.data
    else failed.value = true
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (code.value) check()
})
</script>

<template>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="w-full max-w-md space-y-4">
      <div class="text-center">
        <h1 class="text-2xl font-bold">Validate a certificate</h1>
        <p class="text-sm text-slate-500">Enter the code printed on the certificate (e.g. CRT-2026-8F3A21BC).</p>
      </div>

      <form class="card flex gap-2" @submit.prevent="check">
        <input v-model="code" class="input" placeholder="CRT-2026-XXXXXXXX" aria-label="Certificate code" />
        <button class="btn btn-primary" :disabled="loading">{{ loading ? '...' : 'Validate' }}</button>
      </form>

      <p v-if="failed" class="err text-center">Could not reach the server. Please try again.</p>

      <div v-if="result" class="card text-center ring-2" :class="result.valid ? 'ring-green-500' : 'ring-red-500'">
        <div class="text-4xl">{{ result.valid ? '✅' : '❌' }}</div>
        <h2 class="mt-2 text-xl font-bold" :class="result.valid ? 'text-green-700' : 'text-red-700'">
          {{ result.valid ? 'Valid certificate' : result.status === 'revoked' ? 'Certificate revoked' : 'Certificate not found' }}
        </h2>
        <p v-if="result.message" class="mt-1 text-sm text-slate-500">{{ result.message }}</p>

        <dl v-if="result.certificate" class="mt-4 space-y-2 text-left text-sm">
          <div class="flex justify-between gap-4"><dt class="text-slate-500">Participant</dt><dd class="font-medium">{{ result.certificate.participant_name }}</dd></div>
          <div class="flex justify-between gap-4"><dt class="text-slate-500">Course / event</dt><dd class="font-medium">{{ result.certificate.course_name }}</dd></div>
          <div class="flex justify-between gap-4"><dt class="text-slate-500">Workload</dt><dd class="font-medium">{{ result.certificate.workload_hours }} hours</dd></div>
          <div class="flex justify-between gap-4"><dt class="text-slate-500">Institution</dt><dd class="font-medium">{{ result.certificate.institution_name }}</dd></div>
          <div class="flex justify-between gap-4"><dt class="text-slate-500">Issued on</dt><dd class="font-medium">{{ fmtDate(result.certificate.issued_at) }}</dd></div>
          <div class="flex justify-between gap-4"><dt class="text-slate-500">Code</dt><dd class="font-mono text-xs">{{ result.certificate.code }}</dd></div>
        </dl>
      </div>

      <RouterLink to="/login" class="block text-center text-sm text-blue-600 hover:underline">Staff sign in</RouterLink>
    </div>
  </div>
</template>
