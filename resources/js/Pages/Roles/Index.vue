<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Administration</p>
                <h1 class="text-3xl font-bold text-gray-900 mt-1">Roles & Permissions</h1>
                <p class="text-sm text-gray-500 mt-1">Define access levels and manage permission sets.</p>
            </div>
            <Link
                :href="route('roles.create')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>New Role</span>
            </Link>
        </div>

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="role in roles.data" :key="role.id">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900 capitalize">{{ role.name }}</div>
                            <p class="text-sm text-gray-500">Guard: {{ role.guard_name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="permission in role.permissions"
                                    :key="permission.id"
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700"
                                >
                                    {{ permission.name }}
                                </span>
                                <span v-if="!role.permissions.length" class="text-sm text-gray-400">No permissions</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ role.users_count ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="route('roles.edit', role.id)"
                                    class="text-blue-600 hover:text-blue-900"
                                    title="Edit role"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </Link>
                                <button
                                    v-if="role.name !== 'super-admin'"
                                    type="button"
                                    class="text-red-600 hover:text-red-800"
                                    title="Delete role"
                                    @click="destroy(role)"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0l1-3h6l1 3" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="!roles.data.length" class="p-6 text-center text-gray-500">
                No roles found.
            </div>
        </div>

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <p class="text-sm text-gray-500">
                Showing {{ roles.from || 0 }} to {{ roles.to || 0 }} of {{ roles.total || 0 }} roles
            </p>
            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="link in roles.links"
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

        <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Access Control Matrix</h2>
                    <p class="text-sm text-gray-500">Quickly see which roles have each permission.</p>
                </div>
                <Link
                    :href="route('roles.activity')"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>View Activity Logs</span>
                </Link>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Permission</th>
                            <th
                                v-for="role in roleList"
                                :key="role.id"
                                class="px-4 py-2 text-center font-semibold text-gray-600 capitalize"
                            >
                                {{ role.name }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="permission in permissionsList" :key="permission.id">
                            <td class="px-4 py-2 text-gray-900">{{ permission.name }}</td>
                            <td
                                v-for="role in roleList"
                                :key="role.id + '-' + permission.name"
                                class="px-4 py-2 text-center"
                            >
                                <svg
                                    v-if="roleHasPermission(role, permission.name)"
                                    class="w-5 h-5 text-green-600 mx-auto"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg
                                    v-else
                                    class="w-4 h-4 text-gray-300 mx-auto"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
    roles: { type: Object, required: true },
    permissions: { type: Array, default: () => [] },
});

const deleteForm = useForm({});

const destroy = (role) => {
    if (confirm(`Delete role "${role.name}"?`)) {
        deleteForm.delete(route('roles.destroy', role.id));
    }
};

const roleList = computed(() => props.roles?.data ?? []);
const permissionsList = computed(() => props.permissions ?? []);

const roleHasPermission = (role, permissionName) => {
    return role.permissions?.some(permission => permission.name === permissionName);
};
</script>
