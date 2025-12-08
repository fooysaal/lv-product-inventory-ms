<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4">
        <div class="max-w-2xl w-full text-center">
            <!-- Error Code -->
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-blue-600 mb-2 animate-pulse">404</h1>
                <div class="h-1 w-24 bg-blue-600 mx-auto rounded-full"></div>
            </div>

            <!-- Icon -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-white rounded-full shadow-lg">
                    <i class="fas fa-search text-6xl text-blue-500"></i>
                </div>
            </div>

            <!-- Message -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Page Not Found</h2>
                <p class="text-lg text-gray-600 mb-2">
                    Oops! The page you're looking for doesn't exist.
                </p>
                <p class="text-gray-500">
                    It might have been moved or deleted, or you may have mistyped the URL.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button
                    @click="goBack"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-md hover:shadow-lg"
                >
                    <i class="fas fa-arrow-left"></i>
                    <span>Go Back</span>
                </button>
                <router-link
                    to="/dashboard"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md hover:shadow-lg"
                >
                    <i class="fas fa-home"></i>
                    <span>Go to Dashboard</span>
                </router-link>
            </div>

            <!-- Suggestions -->
            <div class="mt-12 p-6 bg-white rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Links</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <router-link
                        to="/products"
                        class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-blue-50 transition-colors group"
                    >
                        <i class="fas fa-box text-2xl text-blue-600 group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm font-medium text-gray-700">Products</span>
                    </router-link>
                    <router-link
                        to="/categories"
                        class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-blue-50 transition-colors group"
                    >
                        <i class="fas fa-tags text-2xl text-blue-600 group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm font-medium text-gray-700">Categories</span>
                    </router-link>
                    <router-link
                        to="/units"
                        class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-blue-50 transition-colors group"
                    >
                        <i class="fas fa-ruler text-2xl text-blue-600 group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm font-medium text-gray-700">Units</span>
                    </router-link>
                    <router-link
                        to="/users"
                        class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-blue-50 transition-colors group"
                    >
                        <i class="fas fa-users text-2xl text-blue-600 group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm font-medium text-gray-700">Users</span>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'NotFound',
    methods: {
        goBack() {
            if (window.history.length > 1) {
                this.$router.go(-1);
            } else {
                this.$router.push('/dashboard');
            }
        }
    }
};
</script>
