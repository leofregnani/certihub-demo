import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { auth } from './auth'
import './style.css'

window.addEventListener('auth:expired', () => {
  auth.clear()
  router.push('/login')
})

createApp(App).use(router).mount('#app')
