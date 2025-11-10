<template>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white shadow">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Administration</p>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">{{ user.name }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ user.email }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('users.index')"
                        class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold"
                    >
                        Back to list
                    </Link>
                    <Link
                        :href="route('users.edit', user.id)"
                        class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow"
                    >
                        Edit user
                    </Link>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile -->
                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Profile</h2>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full" :class="statusClasses">
                            {{ user.status }}
                        </span>
                    </div>
                    <dl class="space-y-3 text-sm text-gray-600">
                        <div>
                            <dt class="font-medium text-gray-900">Full name</dt>
                            <dd>{{ user.name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900">Email</dt>
                            <dd>{{ user.email }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900">Phone</dt>
                            <dd>{{ user.phone || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900">Role</dt>
                            <dd>
                                <span
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    class="inline-flex items-center px-2 py-1 mr-2 mt-2 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                                >
                                    {{ role.name }}
                                </span>
                                <span v-if="!user.roles?.length" class="text-gray-400">No role assigned</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900">2FA</dt>
                            <dd>{{ user.two_factor_enabled ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900">Last login</dt>
                            <dd>
                                <span v-if="user.last_login_at">{{ formatDate(user.last_login_at) }}</span>
                                <span v-else>Never</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Assignments -->
                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4 lg:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-900">Assignments</h2>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Subdivisions</p>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li v-if="!subdivisions.length" class="text-gray-400">No subdivisions assigned</li>
                                <li v-for="subdivision in subdivisions" :key="subdivision.id" class="flex items-center justify-between">
                                    <span>{{ subdivision.name }}</span>
                                    <span v-if="subdivision.id === user.primary_subdivision_id" class="text-xs text-blue-600 font-semibold">Primary</span>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Gates</p>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li v-if="!gates.length" class="text-gray-400">No gates assigned</li>
                                <li
                                    v-for="gate in gates"
                                    :key="gate.id"
                                    class="flex flex-col"
                                >
                                    <span class="font-medium text-gray-900">{{ gate.name }}</span>
                                    <span class="text-xs text-gray-500">{{ gate.subdivision?.name || 'Unassigned subdivision' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Status Management -->
                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">Status & Access</h2>
                    <form class="space-y-3" @submit.prevent="updateStatus">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Account status</label>
                            <select
                                v-model="statusForm.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <p v-if="statusForm.errors.status" class="mt-1 text-sm text-red-600">{{ statusForm.errors.status }}</p>
                        </div>
                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-50"
                            :disabled="statusForm.processing"
                        >
                            Update status
                        </button>
                    </form>
                </div>

                <!-- Password Reset -->
                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4 lg:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-900">Reset Password</h2>
                    <form class="grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="resetPassword">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                            <input
                                v-model="passwordForm.password"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                            <input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                        <div class="md:col-span-2 flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-900 text-white font-semibold hover:bg-gray-800 disabled:opacity-50"
                                :disabled="passwordForm.processing"
                            >
                                Reset password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activity Logs -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                    <p class="text-sm text-gray-500">Last 10 events</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-if="!activityLogs.length" class="py-4 text-sm text-gray-500">No activity recorded for this user.</li>
                    <li v-for="log in activityLogs" :key="log.id" class="py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ log.description }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(log.created_at) }}</p>
                        </div>
                        <span class="text-xs text-gray-400 uppercase tracking-wide">{{ log.event || 'log' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    user: { type: Object, required: true },
    subdivisions: { type: Array, default: () => [] },
    gates: { type: Array, default: () => [] },
    activityLogs: { type: Array, default: () => [] },
});

const statusForm = useForm({
    status: props.user.status ?? 'active',
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

const updateStatus = () => {
    statusForm.post(route('users.change-status', props.user.id), {
        preserveScroll: true,
    });
};

const resetPassword = () => {
    passwordForm.post(route('users.reset-password', props.user.id), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

const formatDate = (value) => {
    if (!value) {
        return '';
    }
    return new Date(value).toLocaleString();
};

const statusClasses = computed(() => {
    const map = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-gray-100 text-gray-700',
        suspended: 'bg-red-100 text-red-700',
    };
    return map[props.user.status] || 'bg-gray-100 text-gray-700';
});
</script>
