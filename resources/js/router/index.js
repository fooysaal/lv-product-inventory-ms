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

    // User Routes
    {
        path: '/users',
        name: 'users.index',
        component: () => import('@/pages/Users/Index.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Users'
        }
    },
    {
        path: '/users/create',
        name: 'users.create',
        component: () => import('@/pages/Users/Form.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Create User'
        }
    },
    {
        path: '/users/:id/edit',
        name: 'users.edit',
        component: () => import('@/pages/Users/Form.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Edit User'
        }
    },

    // Category Routes
    {
        path: '/categories',
        name: 'categories.index',
        component: () => import('@/pages/Categories/Index.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Categories'
        }
    },
    {
        path: '/categories/create',
        name: 'categories.create',
        component: () => import('@/pages/Categories/Form.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Create Category'
        }
    },
    {
        path: '/categories/:id/edit',
        name: 'categories.edit',
        component: () => import('@/pages/Categories/Form.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Edit Category'
        }
    },

    // Unit Routes
    {
        path: '/units',
        name: 'units.index',
        component: () => import('@/pages/Units/Index.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Units'
        }
    },
    {
        path: '/units/create',
        name: 'units.create',
        component: () => import('@/pages/Units/Form.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Create Unit'
        }
    },
    {
        path: '/units/:id/edit',
        name: 'units.edit',
        component: () => import('@/pages/Units/Form.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Edit Unit'
        }
    },

    // Product Routes
    {
        path: '/products',
        name: 'products.index',
        component: () => import('@/pages/Products/Index.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Products'
        }
    },
    {
        path: '/products/create',
        name: 'products.create',
        component: () => import('@/pages/Products/Form.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Create Product'
        }
    },
    {
        path: '/products/:id/edit',
        name: 'products.edit',
        component: () => import('@/pages/Products/Form.vue'),
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Edit Product'
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
