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
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700"
            >
                New Role
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
                            {{ role.users_count ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                            <Link :href="route('roles.edit', role.id)" class="text-blue-600 hover:text-blue-900">Edit</Link>
                            <button
                                v-if="role.name !== 'super-admin'"
                                type="button"
                                class="text-red-600 hover:text-red-800"
                                @click="destroy(role)"
                            >
                                Delete
                            </button>
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
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    roles: Object,
});

const deleteForm = useForm({});

const destroy = (role) => {
    if (confirm(`Delete role "${role.name}"?`)) {
        deleteForm.delete(route('roles.destroy', role.id));
    }
};
</script>
