import { createRouter, createWebHistory } from 'vue-router'
import { auth } from './auth'

const routes = [
  // Public pages
  { path: '/login', component: () => import('./views/LoginView.vue'), meta: { public: true } },
  { path: '/forgot-password', component: () => import('./views/ForgotPasswordView.vue'), meta: { public: true } },
  { path: '/reset-password', component: () => import('./views/ResetPasswordView.vue'), meta: { public: true } },
  { path: '/validate/:code?', component: () => import('./views/ValidateView.vue'), meta: { public: true } },

  // Authenticated pages
  { path: '/', component: () => import('./views/DashboardView.vue') },
  { path: '/participants', component: () => import('./views/ParticipantsView.vue') },
  { path: '/courses', component: () => import('./views/CoursesView.vue') },
  { path: '/certificates', component: () => import('./views/CertificatesView.vue') },

  // Admin-only pages
  { path: '/users', component: () => import('./views/UsersView.vue'), meta: { admin: true } },
  { path: '/logs', component: () => import('./views/LogsView.vue'), meta: { admin: true } },

  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach((to) => {
  if (!to.meta.public && !auth.loggedIn) return '/login'
  if (to.meta.admin && !auth.isAdmin) return '/'
  if (['/login', '/forgot-password'].includes(to.path) && auth.loggedIn) return '/'
})

export default router
