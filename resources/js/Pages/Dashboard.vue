<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-indigo-600">SubdiPass</h1>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <Link
                                :href="route('dashboard')"
                                :class="$page.url === '/dashboard' ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Dashboard
                            </Link>

                            <!-- Passes (Employee, Admin, Super-admin) -->
                            <Link
                                v-if="hasRole(['employee', 'admin', 'super-admin'])"
                                :href="route('passes.index')"
                                :class="$page.url.startsWith('/passes') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Passes
                            </Link>

                            <!-- Pass Types (Admin, Super-admin) -->
                            <Link
                                v-if="hasRole(['admin', 'super-admin'])"
                                :href="route('pass-types.index')"
                                :class="$page.url.startsWith('/pass-types') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Pass Types
                            </Link>

                            <!-- Users (Admin, Super-admin) -->
                            <Link
                                v-if="hasRole(['admin', 'super-admin'])"
                                :href="route('users.index')"
                                :class="$page.url.startsWith('/users') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Users
                            </Link>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-700">{{ $page.props.auth.user.name }}</span>
                                <form method="POST" action="/logout" @submit.prevent="logout">
                                    <button type="submit" class="text-sm text-gray-700 hover:text-gray-900">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-2xl font-bold mb-4">Welcome to SubdiPass Dashboard!</h2>
                        <p class="mb-4">You're logged in successfully.</p>

                        <!-- User Info Card -->
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-lg mb-2">Your Account Information</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $page.props.auth.user.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $page.props.auth.user.email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Roles</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span v-for="role in $page.props.auth.user.roles" :key="role.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mr-2">
                                            {{ role.name }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Quick Stats -->
                        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">Total Passes</dt>
                                                <dd class="text-lg font-medium text-gray-900">0</dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">Active Passes</dt>
                                                <dd class="text-lg font-medium text-gray-900">0</dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">Scans Today</dt>
                                                <dd class="text-lg font-medium text-gray-900">0</dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

// Helper function to check if user has specific roles
const hasRole = (roles) => {
    const userRoles = page.props.auth.user.roles.map(role => role.name);
    return roles.some(role => userRoles.includes(role));
};

const logout = () => {
    router.post('/logout');
};
</script>
