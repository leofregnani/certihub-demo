<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { auth } from '../auth'

const route = useRoute()
const router = useRouter()
const open = ref(false)

const links = computed(() => {
  const base = [
    { to: '/', label: 'Dashboard' },
    { to: '/participants', label: 'Participants' },
    { to: '/courses', label: 'Courses & events' },
    { to: '/certificates', label: 'Certificates' },
  ]
  if (auth.isAdmin) {
    base.push({ to: '/users', label: 'Users' }, { to: '/logs', label: 'Audit logs' })
  }
  return base
})

watch(() => route.fullPath, () => (open.value = false))

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen lg:flex">
    <!-- Mobile top bar -->
    <header class="flex items-center justify-between bg-slate-900 px-4 py-3 text-white lg:hidden">
      <span class="font-bold">CertiHub Demo</span>
      <button class="rounded p-2 hover:bg-slate-800" aria-label="Toggle menu" @click="open = !open">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </header>

    <!-- Sidebar -->
    <aside
      class="bg-slate-900 text-slate-300 lg:block lg:w-60 lg:shrink-0"
      :class="open ? 'block' : 'hidden'"
    >
      <div class="hidden px-6 py-6 text-xl font-bold text-white lg:block">CertiHub Demo</div>
      <nav class="space-y-1 px-3 pb-4 pt-2 lg:pt-0">
        <RouterLink
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-slate-800"
          active-class="bg-blue-600 text-white hover:bg-blue-600"
          :exact-active-class="link.to === '/' ? 'bg-blue-600 text-white hover:bg-blue-600' : ''"
        >
          {{ link.label }}
        </RouterLink>
        <RouterLink to="/validate" class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-800">
          Public validation page ↗
        </RouterLink>
      </nav>
      <div class="border-t border-slate-800 px-6 py-4 text-sm">
        <div class="font-medium text-white">{{ auth.state.user?.name }}</div>
        <div class="text-xs capitalize text-slate-400">{{ auth.state.user?.role }}</div>
        <button class="mt-3 text-xs text-blue-300 hover:underline" @click="logout">Sign out</button>
      </div>
    </aside>

    <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">
      <slot />
    </main>
  </div>
</template>
