<template>
    <div class="space-y-12">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-4">Welcome to SubdiPass Dashboard!</h2>
                <p class="mb-4">You're logged in successfully.</p>

                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-lg mb-2">Your Account Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ page.props.auth.user.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ page.props.auth.user.email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Role</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span v-for="role in page.props.auth.user.roles" :key="role.id" class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mr-2">
                                    {{ role.name }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Subdivision Access</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span v-if="page.props.auth.user.subdivision_ids">
                                    {{ JSON.parse(page.props.auth.user.subdivision_ids)?.length || 0 }} assigned subdivisions
                                </span>
                                <span v-else>Not assigned</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Link
                        v-if="hasRole(['employee', 'admin', 'super-admin'])"
                        :href="route('passes.create')"
                        class="block bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 text-center"
                    >
                        <div class="text-3xl">🎫</div>
                        <div class="mt-2 font-semibold text-blue-800">Create Pass</div>
                        <p class="text-sm text-blue-600">Generate a new visitor or job order pass.</p>
                    </Link>

                    <Link
                        v-if="hasRole(['admin', 'super-admin'])"
                        :href="route('users.create')"
                        class="block bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 text-center"
                    >
                        <div class="text-3xl">👤</div>
                        <div class="mt-2 font-semibold text-green-800">Add User</div>
                        <p class="text-sm text-green-600">Invite an admin, guard, or staff member.</p>
                    </Link>

                    <Link
                        v-if="hasRole(['admin', 'super-admin'])"
                        :href="route('pass-types.create')"
                        class="block bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-4 text-center"
                    >
                        <div class="text-3xl">📋</div>
                        <div class="mt-2 font-semibold text-purple-800">Pass Types</div>
                        <p class="text-sm text-purple-600">Configure available pass types.</p>
                    </Link>

                    <Link
                        v-if="hasRole(['employee', 'admin', 'super-admin'])"
                        :href="route('passes.index')"
                        class="block bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-lg p-4 text-center"
                    >
                        <div class="text-3xl">📄</div>
                        <div class="mt-2 font-semibold text-yellow-800">View Passes</div>
                        <p class="text-sm text-yellow-600">Browse and manage existing passes.</p>
                    </Link>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l6 6-6 6M21 7l-6 6 6 6" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pending Approvals</dt>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c.638 0 3 0 3 4a3 3 0 11-6 0c0-4 2.362-4 3-4z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 11h16M4 15h16" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Registered Visitors</dt>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.104 0 2 .896 2 2s-.896 2-2 2-2-.896-2-2 .896-2 2-2zm0-6C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Guards</dt>
                                <dd class="text-lg font-medium text-gray-900">0</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const page = usePage();

const hasRole = (roles) => {
    const userRoles = page.props.auth.user.roles.map(role => role.name);
    return roles.some(role => userRoles.includes(role));
};
</script>
