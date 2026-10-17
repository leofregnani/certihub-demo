import { onMounted, reactive, ref, watch } from 'vue'
import api from './api'

/**
 * Paginated + filterable list backed by a Laravel paginator endpoint.
 * Changing any filter resets to page 1 (debounced).
 */
export function useList(url, initialFilters = {}) {
  const items = ref([])
  const loading = ref(false)
  const page = ref(1)
  const lastPage = ref(1)
  const total = ref(0)
  const filters = reactive({ search: '', ...initialFilters })

  async function load() {
    loading.value = true
    try {
      const params = { page: page.value }
      for (const [key, value] of Object.entries(filters)) {
        if (value !== '' && value !== null) params[key] = value
      }
      const { data } = await api.get(url, { params })
      items.value = data.data
      lastPage.value = data.last_page
      total.value = data.total
    } finally {
      loading.value = false
    }
  }

  function go(p) {
    page.value = Math.min(Math.max(p, 1), lastPage.value)
    load()
  }

  let timer
  watch(filters, () => {
    clearTimeout(timer)
    timer = setTimeout(() => {
      page.value = 1
      load()
    }, 300)
  })

  onMounted(load)

  return { items, loading, page, lastPage, total, filters, load, go }
}
