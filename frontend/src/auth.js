import { reactive } from 'vue'
import api from './api'

function readUser() {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null')
  } catch {
    return null
  }
}

const state = reactive({
  token: localStorage.getItem('token'),
  user: readUser(),
})

export const auth = {
  state,

  get loggedIn() {
    return !!state.token
  },

  get isAdmin() {
    return state.user?.role === 'admin'
  },

  async login(email, password) {
    const { data } = await api.post('/auth/login', { email, password })
    state.token = data.token
    state.user = data.user
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
  },

  async logout() {
    try {
      await api.post('/auth/logout')
    } catch {
      // already expired: nothing to do
    }
    this.clear()
  },

  clear() {
    state.token = null
    state.user = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  },
}
