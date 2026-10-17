import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Expired or revoked token: tell the app to sign out (handled in main.js).
    const isLogin = error.config?.url?.includes('/auth/login')
    if (error.response?.status === 401 && !isLogin) {
      window.dispatchEvent(new Event('auth:expired'))
    }
    return Promise.reject(error)
  },
)

/** Extracts field errors ({field: [msgs]}) and a general message from an axios error. */
export function parseError(error) {
  const data = error.response?.data
  return {
    message: data?.message || error.message || 'Something went wrong.',
    fields: data?.errors || {},
  }
}

export default api
