<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Administration</p>
                        <h1 class="text-3xl font-bold text-gray-900 mt-1">User Management</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage system access, assignments, and account status.</p>
                    </div>
                    <Link
                        :href="route('users.create')"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition shadow"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New User
                    </Link>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            v-model="filterForm.search"
                            type="text"
                            placeholder="Name, email, phone..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @input="debouncedSearch"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select
                            v-model="filterForm.role"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All roles</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.name"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select
                            v-model="filterForm.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subdivision</label>
                        <select
                            v-model="filterForm.subdivision_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All subdivisions</option>
                            <option
                                v-for="subdivision in subdivisions"
                                :key="subdivision.id"
                                :value="subdivision.id"
                            >
                                {{ subdivision.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                    <p class="text-sm text-gray-500">
                        {{ hasActiveFilters ? 'Filters active' : 'Showing all users' }}
                    </p>
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="text-sm font-semibold text-blue-600 hover:text-blue-700"
                        @click="resetFilters"
                    >
                        Clear filters
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <template v-if="userList.length">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assignments</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in userList" :key="user.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-gray-900">{{ user.name }}</span>
                                        <span class="text-sm text-gray-500">{{ user.email }}</span>
                                        <span v-if="user.phone" class="text-xs text-gray-400">{{ user.phone }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="role in user.roles"
                                            :key="role.id"
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                                        >
                                            {{ role.name }}
                                        </span>
                                        <span v-if="!user.roles?.length" class="text-sm text-gray-400">No role</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ formatAssignments(user) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-3 py-1 text-xs font-semibold rounded-full"
                                        :class="getStatusClass(user.status)"
                                    >
                                        {{ user.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="route('users.show', user.id)"
                                            class="text-blue-600 hover:text-blue-900"
                                            title="View user"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('users.edit', user.id)"
                                            class="text-yellow-600 hover:text-yellow-900"
                                            title="Edit user"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </Link>
                                        <button
                                            type="button"
                                            class="text-red-600 hover:text-red-800"
                                            title="Delete user"
                                            @click="destroyUser(user)"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0l1-3h6l1 3"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </template>
                <div v-else class="p-8 text-center text-gray-500">
                    No users found. Adjust your filters or create a new user.
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-gray-500">
                    Showing
                    <span class="font-semibold text-gray-900">{{ users.from || 0 }}</span>
                    to
                    <span class="font-semibold text-gray-900">{{ users.to || 0 }}</span>
                    of
                    <span class="font-semibold text-gray-900">{{ users.total || 0 }}</span>
                    users
                </p>
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="link in users.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1 rounded border text-sm"
                        :class="[
                            link.active
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'border-gray-300 text-gray-600 hover:bg-gray-50',
                            !link.url && 'pointer-events-none text-gray-400 bg-gray-100'
                        ]"
                        preserve-scroll
                        preserve-state
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    users: { type: Object, required: true },
    roles: { type: Array, default: () => [] },
    subdivisions: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});


const filterForm = ref({
    search: props.filters?.search ?? '',
    role: props.filters?.role ?? '',
    status: props.filters?.status ?? '',
    subdivision_id: props.filters?.subdivision_id ?? '',
});

const applyFilters = () => {
    router.get(route('users.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

let debounceTimer;
const debouncedSearch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        applyFilters();
    }, 400);
};

const resetFilters = () => {
    filterForm.value.search = '';
    filterForm.value.role = '';
    filterForm.value.status = '';
    filterForm.value.subdivision_id = '';
    applyFilters();
};

const hasActiveFilters = computed(() => {
    return Boolean(
        filterForm.value.search ||
        filterForm.value.role ||
        filterForm.value.status ||
        filterForm.value.subdivision_id
    );
});

const userList = computed(() => props.users?.data ?? []);
const deleteForm = useForm({});

const destroyUser = (user) => {
    if (confirm(`Delete ${user.name}? This action cannot be undone.`)) {
        deleteForm.delete(route('users.destroy', user.id), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const formatAssignments = (user) => {
    const subdivisions = Array.isArray(user.subdivision_ids) ? user.subdivision_ids : [];
    const gates = Array.isArray(user.gate_ids) ? user.gate_ids : [];

    if (!subdivisions.length && !gates.length) {
        return 'No assignments';
    }

    const parts = [];
    if (subdivisions.length) {
        parts.push(`${subdivisions.length} subdivision${subdivisions.length > 1 ? 's' : ''}`);
    }
    if (gates.length) {
        parts.push(`${gates.length} gate${gates.length > 1 ? 's' : ''}`);
    }

    return parts.join(' • ');
};

const getStatusClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-gray-100 text-gray-700',
        suspended: 'bg-red-100 text-red-700',
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};
</script>
