import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../pages/HomePage.vue'),
  },
  /* Auth — всегда доступны */
  {
    path: '/admin/login',
    name: 'admin.login',
    component: () => import('../pages/admin/auth/LoginPage.vue'),
    meta: { guest: true },
  },
  {
    path: '/admin/register',
    name: 'admin.register',
    component: () => import('../pages/admin/auth/RegisterPage.vue'),
    meta: { guest: true },
  },
  {
    path: '/admin/forgot-password',
    name: 'admin.forgot-password',
    component: () => import('../pages/admin/auth/ForgotPasswordPage.vue'),
    meta: { guest: true },
  },
  {
    path: '/admin/reset-password',
    name: 'admin.reset-password',
    component: () => import('../pages/admin/auth/ResetPasswordPage.vue'),
    meta: { guest: true },
  },
  /* /admin/* — только manager и administrator */
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdminAccess: true },
    children: [
      {
        path: '',
        name: 'admin.dashboard',
        component: () => import('../pages/admin/DashboardPage.vue'),
        meta: { title: 'Панель управления' },
      },
      {
        path: 'subscribers',
        name: 'admin.subscribers',
        component: () => import('../pages/admin/SubscribersPage.vue'),
        meta: { title: 'Подписчики' },
      },
      {
        path: 'subscribers/:id',
        name: 'admin.subscriber',
        component: () => import('../pages/admin/SubscriberDetailPage.vue'),
        meta: { title: 'Подписчик' },
      },
      {
        path: 'subscription-applications',
        name: 'admin.subscription-applications',
        component: () => import('../pages/admin/SubscriptionApplicationsPage.vue'),
        meta: { title: 'Заявки на подписку' },
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, _from, next) => {
  const auth = useAuthStore();

  if (!auth.initialized) {
    try {
      await auth.fetchUser();
    } catch {
      auth.initialized = true;
    }
  }

  const isAuth = auth.isAuthenticated;
  const hasAdminAccess = auth.hasAdminPanelAccess;
  const isGuestRoute = to.meta.guest === true;
  const requiresAuth = to.meta.requiresAuth === true;
  const requiresAdminAccess = to.meta.requiresAdminAccess === true;

  if (isGuestRoute) {
    if (isAuth && hasAdminAccess) {
      return next({ name: 'admin.dashboard' });
    }
    return next();
  }

  if (requiresAuth || requiresAdminAccess) {
    if (!isAuth) {
      return next({ name: 'admin.login', query: { redirect: to.fullPath } });
    }
    if (requiresAdminAccess && !hasAdminAccess) {
      return next({ path: '/' });
    }
  }

  next();
});

export default router;
