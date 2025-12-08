import { createRouter, createWebHistory } from 'vue-router';
import AuthLayout from '@/layouts/AuthLayout.vue';
import DefaultLayout from '@/layouts/DefaultLayout.vue';

const routes = [
    // Auth Routes
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/Auth/Login.vue'),
        meta: {
            layout: AuthLayout,
            requiresGuest: true
        }
    },

    // Dashboard Routes (example)
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/pages/Dashboard/Index.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Dashboard'
        }
    },

    // User Type Routes
    {
        path: '/user-types',
        name: 'user-types.index',
        component: () => import('@/pages/UserTypes/Index.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'User Types'
        }
    },
    {
        path: '/user-types/create',
        name: 'user-types.create',
        component: () => import('@/pages/UserTypes/Form.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Create User Type'
        }
    },
    {
        path: '/user-types/:id/edit',
        name: 'user-types.edit',
        component: () => import('@/pages/UserTypes/Form.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Edit User Type'
        }
    },

    // Default redirect
    {
        path: '/',
        name: 'landing',
        component: () => import('@/pages/Landing.vue'),
        meta: {
            layout: AuthLayout,
            requiresGuest: true
        }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation guards
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    const isAuthenticated = !!token;

    // Check if route requires authentication
    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'login' });
    }
    // Check if route is for guests only
    else if (to.meta.requiresGuest && isAuthenticated) {
        next({ name: 'dashboard' });
    }
    // Proceed to route
    else {
        next();
    }
});

export default router;
