@extends('layouts.app')

@section('content')
    <!-- Header -->
    <header
        class="sticky top-0 z-50 w-full bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm shadow-md border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div
                        class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-red-600 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 50 52">
                            <path
                                d="M49.626 11.564a.809.809 0 0 1 .028.209v10.972a.8.8 0 0 1-.402.694l-9.209 5.302V39.25c0 .286-.152.55-.4.694L20.42 51.01c-.044.025-.092.041-.14.058-.018.006-.035.017-.054.022a.805.805 0 0 1-.41 0c-.022-.006-.042-.018-.063-.026-.044-.016-.09-.03-.132-.054L.402 39.944A.801.801 0 0 1 0 39.25V6.334c0-.072.01-.142.028-.21.006-.023.02-.044.028-.067.015-.042.029-.085.051-.124.015-.026.037-.047.055-.071.023-.032.044-.065.071-.093.023-.023.053-.04.079-.06.029-.024.055-.05.088-.069h.001l9.61-5.533a.802.802 0 0 1 .8 0l9.61 5.533h.002c.032.02.059.045.088.068.026.02.055.038.078.06.028.029.048.062.072.094.017.024.04.045.054.071.023.04.036.082.052.124.008.023.022.044.028.068a.809.809 0 0 1 .028.209v20.559l8.008-4.611v-10.51c0-.07.01-.141.028-.208.007-.024.02-.045.028-.068.016-.042.03-.085.052-.124.015-.026.037-.047.054-.071.024-.032.044-.065.072-.093.023-.023.052-.04.078-.06.03-.024.056-.05.088-.069h.001l9.611-5.533a.801.801 0 0 1 .8 0l9.61 5.533c.034.02.06.045.09.068.025.02.054.038.077.06.028.029.048.062.072.094.018.024.04.045.054.071.023.039.036.082.052.124.009.023.022.044.028.068zm-1.574 10.718v-9.124l-3.363 1.936-4.646 2.675v9.124l8.01-4.611zm-9.61 16.505v-9.13l-4.57 2.61-13.05 7.448v9.216l17.62-10.144zM1.602 7.719v31.068L19.22 48.93v-9.214l-9.204-5.209-.003-.002-.004-.002c-.031-.018-.057-.044-.086-.066-.025-.02-.054-.036-.076-.058l-.002-.003c-.026-.025-.044-.056-.066-.084-.02-.027-.044-.05-.06-.078l-.001-.003c-.018-.03-.029-.066-.042-.1-.013-.03-.03-.058-.038-.09v-.001c-.01-.038-.012-.078-.016-.117-.004-.03-.012-.06-.012-.09v-.002-21.481L4.965 9.654 1.602 7.72zm8.81-5.994L2.405 6.334l8.005 4.609 8.006-4.61-8.006-4.608zm4.164 28.764l4.645-2.674V7.719l-3.363 1.936-4.646 2.675v20.096l3.364-1.937zM39.243 7.164l-8.006 4.609 8.006 4.609 8.005-4.61-8.005-4.608zm-.801 10.605l-4.646-2.675-3.363-1.936v9.124l4.645 2.674 3.364 1.937v-9.124zM20.02 38.33l11.743-6.704 5.87-3.35-8-4.606-9.211 5.303-8.395 4.833 7.993 4.524z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Product Inventory System
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">Warehouse Management
                            Solution</p>
                    </div>
                </div>
                <div>
                    <a href="#"
                        class="inline-flex items-center gap-2 px-6 py-2.5 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                            </path>
                        </svg>
                        Login
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16">
        <div
            class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-800">
            <!-- Hero Section -->
            <div
                class="relative bg-gradient-to-br from-red-600 via-red-700 to-red-800 px-6 sm:px-8 lg:px-12 py-12 sm:py-16 lg:py-20 text-white overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-red-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-64 h-64 bg-red-900/20 rounded-full blur-3xl"></div>
                <div class="relative z-10">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-4 sm:mb-6">Comprehensive Inventory
                        Management System</h2>
                    <p class="text-base sm:text-lg lg:text-xl text-red-50 max-w-3xl leading-relaxed">Built with Laravel
                        and Vue.js for managing product stock across multiple warehouses with role-based access control
                    </p>
                </div>
            </div>

            <!-- Overview Section -->
            <div class="px-6 sm:px-8 lg:px-12 py-10 sm:py-12 lg:py-16">
                <div class="mb-12 sm:mb-16">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="h-1 w-12 bg-gradient-to-r from-red-600 to-red-400 rounded-full"></div>
                        <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Overview</h3>
                    </div>
                    <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-4xl">
                        This system enables efficient stock management with user-specific tasks for stock in/out
                        operations across various warehouses.
                        It implements a hierarchical approval workflow with dedicated roles for stock management.
                    </p>
                </div>

                <!-- Features Section -->
                <div class="mb-12 sm:mb-16">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="h-1 w-12 bg-gradient-to-r from-red-600 to-red-400 rounded-full"></div>
                        <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Key Features</h3>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-6 lg:gap-8">
                        <div
                            class="group bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-850 rounded-xl p-6 sm:p-8 border border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-red-800 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="flex items-start gap-5">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center shadow-lg group-hover:shadow-red-500/50 transition-shadow">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        Multi-Warehouse Support</h4>
                                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                                        Manage inventory across multiple warehouse locations with ease</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="group bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-850 rounded-xl p-6 sm:p-8 border border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-red-800 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="flex items-start gap-5">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg group-hover:shadow-blue-500/50 transition-shadow">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        Role-Based Access Control</h4>
                                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                                        Dedicated roles for Stock Executives and Stock Managers</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="group bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-850 rounded-xl p-6 sm:p-8 border border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-red-800 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="flex items-start gap-5">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg group-hover:shadow-green-500/50 transition-shadow">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        Stock Operations</h4>
                                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                                        Handle stock in/out entries with approval workflow</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="group bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-850 rounded-xl p-6 sm:p-8 border border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-red-800 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="flex items-start gap-5">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg group-hover:shadow-purple-500/50 transition-shadow">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        Warehouse-Specific Management</h4>
                                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                                        Each warehouse has dedicated managers and executives</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Roles Section -->
                <div class="mb-12 sm:mb-16">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="h-1 w-12 bg-gradient-to-r from-red-600 to-red-400 rounded-full"></div>
                        <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">User Roles</h3>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div
                            class="group bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800 rounded-xl p-6 sm:p-8 border border-blue-200 dark:border-blue-800 hover:border-blue-400 dark:hover:border-blue-600 transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/20 hover:-translate-y-1">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg group-hover:shadow-blue-500/50 transition-shadow ring-4 ring-blue-100 dark:ring-blue-900/50">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xl sm:text-2xl font-bold text-blue-700 dark:text-blue-400 mb-2">
                                        Stock Executive</h4>
                                    <p class="text-sm sm:text-base text-gray-700 dark:text-gray-300 leading-relaxed">
                                        Creates and manages stock entries for daily operations</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="group bg-gradient-to-br from-purple-50 to-white dark:from-purple-900/20 dark:to-gray-800 rounded-xl p-6 sm:p-8 border border-purple-200 dark:border-purple-800 hover:border-purple-400 dark:hover:border-purple-600 transition-all duration-300 hover:shadow-xl hover:shadow-purple-500/20 hover:-translate-y-1">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg group-hover:shadow-purple-500/50 transition-shadow ring-4 ring-purple-100 dark:ring-purple-900/50">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4
                                        class="text-xl sm:text-2xl font-bold text-purple-700 dark:text-purple-400 mb-2">
                                        Stock Manager</h4>
                                    <p class="text-sm sm:text-base text-gray-700 dark:text-gray-300 leading-relaxed">
                                        Reviews and approves/rejects stock transactions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tech Stack Section -->
                <div>
                    <div class="flex items-center gap-3 mb-8">
                        <div class="h-1 w-12 bg-gradient-to-r from-red-600 to-red-400 rounded-full"></div>
                        <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Tech Stack</h3>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <div class="group">
                            <span
                                class="inline-flex items-center px-5 py-3 rounded-xl text-sm font-semibold bg-gradient-to-br from-red-50 to-red-100 text-red-700 dark:from-red-900/30 dark:to-red-900/20 dark:text-red-300 border border-red-200 dark:border-red-800 hover:border-red-400 dark:hover:border-red-600 transition-all duration-300 hover:shadow-lg hover:shadow-red-500/20 hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 50 52">
                                    <path
                                        d="M49.626 11.564a.809.809 0 0 1 .028.209v10.972a.8.8 0 0 1-.402.694l-9.209 5.302V39.25c0 .286-.152.55-.4.694L20.42 51.01c-.044.025-.092.041-.14.058-.018.006-.035.017-.054.022a.805.805 0 0 1-.41 0c-.022-.006-.042-.018-.063-.026-.044-.016-.09-.03-.132-.054L.402 39.944A.801.801 0 0 1 0 39.25V6.334c0-.072.01-.142.028-.21.006-.023.02-.044.028-.067.015-.042.029-.085.051-.124.015-.026.037-.047.055-.071.023-.032.044-.065.071-.093.023-.023.053-.04.079-.06.029-.024.055-.05.088-.069h.001l9.61-5.533a.802.802 0 0 1 .8 0l9.61 5.533h.002c.032.02.059.045.088.068.026.02.055.038.078.06.028.029.048.062.072.094.017.024.04.045.054.071.023.04.036.082.052.124.008.023.022.044.028.068a.809.809 0 0 1 .028.209v20.559l8.008-4.611v-10.51c0-.07.01-.141.028-.208.007-.024.02-.045.028-.068.016-.042.03-.085.052-.124.015-.026.037-.047.054-.071.024-.032.044-.065.072-.093.023-.023.052-.04.078-.06.03-.024.056-.05.088-.069h.001l9.611-5.533a.801.801 0 0 1 .8 0l9.61 5.533c.034.02.06.045.09.068.025.02.054.038.077.06.028.029.048.062.072.094.018.024.04.045.054.071.023.039.036.082.052.124.009.023.022.044.028.068zm-1.574 10.718v-9.124l-3.363 1.936-4.646 2.675v9.124l8.01-4.611zm-9.61 16.505v-9.13l-4.57 2.61-13.05 7.448v9.216l17.62-10.144zM1.602 7.719v31.068L19.22 48.93v-9.214l-9.204-5.209-.003-.002-.004-.002c-.031-.018-.057-.044-.086-.066-.025-.02-.054-.036-.076-.058l-.002-.003c-.026-.025-.044-.056-.066-.084-.02-.027-.044-.05-.06-.078l-.001-.003c-.018-.03-.029-.066-.042-.1-.013-.03-.03-.058-.038-.09v-.001c-.01-.038-.012-.078-.016-.117-.004-.03-.012-.06-.012-.09v-.002-21.481L4.965 9.654 1.602 7.72zm8.81-5.994L2.405 6.334l8.005 4.609 8.006-4.61-8.006-4.608zm4.164 28.764l4.645-2.674V7.719l-3.363 1.936-4.646 2.675v20.096l3.364-1.937zM39.243 7.164l-8.006 4.609 8.006 4.609 8.005-4.61-8.005-4.608zm-.801 10.605l-4.646-2.675-3.363-1.936v9.124l4.645 2.674 3.364 1.937v-9.124zM20.02 38.33l11.743-6.704 5.87-3.35-8-4.606-9.211 5.303-8.395 4.833 7.993 4.524z" />
                                </svg>
                                Laravel
                            </span>
                        </div>
                        <div class="group">
                            <span
                                class="inline-flex items-center px-5 py-3 rounded-xl text-sm font-semibold bg-gradient-to-br from-green-50 to-green-100 text-green-700 dark:from-green-900/30 dark:to-green-900/20 dark:text-green-300 border border-green-200 dark:border-green-800 hover:border-green-400 dark:hover:border-green-600 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/20 hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12,2L3,7L5,18.23L12,22L19,18.23L21,7L12,2M12,4.15L13.73,14.88L12,15.73L10.27,14.88L12,4.15Z" />
                                </svg>
                                Vue.js
                            </span>
                        </div>
                        <div class="group">
                            <span
                                class="inline-flex items-center px-5 py-3 rounded-xl text-sm font-semibold bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-700 dark:from-indigo-900/30 dark:to-indigo-900/20 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:border-indigo-400 dark:hover:border-indigo-600 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/20 hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                </svg>
                                PHP Framework
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-gray-500 dark:text-gray-400 text-sm">
                &copy; 2025 Product Inventory System. Built with Laravel & Vue.js
            </p>
        </div>
    </footer>
@endsection
