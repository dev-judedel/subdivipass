<template>
    <div class="space-y-8">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Edit Role</p>
                <h1 class="text-3xl font-bold text-gray-900 mt-1">Update {{ role.name }}</h1>
                <p class="text-sm text-gray-500 mt-1">Modify the role details and adjust permissions.</p>
            </div>
            <Link
                :href="route('roles.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
            >
                Back to roles
            </Link>
        </div>

        <form class="bg-white rounded-lg shadow-sm p-6 space-y-6" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role name</label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Permissions</label>
                    <button
                        type="button"
                        class="text-sm text-blue-600 hover:text-blue-700"
                        @click="toggleAll()"
                    >
                        {{ isAllSelected ? 'Clear all' : 'Select all' }}
                    </button>
                </div>
                <div class="space-y-4">
                    <div
                        v-for="(items, group) in groupedPermissions"
                        :key="group"
                        class="border border-gray-200 rounded-lg"
                    >
                        <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-700 capitalize">{{ group }}</p>
                            <button
                                type="button"
                                class="text-xs text-blue-600 hover:text-blue-700"
                                @click="toggleGroup(group)"
                            >
                                {{ isGroupSelected(group) ? 'Deselect' : 'Select' }}
                            </button>
                        </div>
                        <div class="px-4 py-3 grid sm:grid-cols-2 gap-2">
                            <label
                                v-for="permission in items"
                                :key="permission.name"
                                class="flex items-start space-x-2 text-sm text-gray-600 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    :value="permission.name"
                                    v-model="form.permissions"
                                    class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span>{{ permission.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <p v-if="form.errors.permissions" class="mt-2 text-sm text-red-600">{{ form.errors.permissions }}</p>
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                <button
                    v-if="role.name !== 'super-admin'"
                    type="button"
                    class="text-sm text-red-600 hover:text-red-700"
                    @click="destroy"
                >
                    Delete role
                </button>
                <div class="flex gap-3 ml-auto">
                    <Link
                        :href="route('roles.index')"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : 'Save changes' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    role: { type: Object, required: true },
    permissions: { type: Array, default: () => [] },
});

const form = useForm({
    name: props.role.name,
    permissions: props.role.permissions?.map(permission => permission.name) || [],
});

const groupedPermissions = computed(() => {
    return props.permissions.reduce((groups, permission) => {
        const [group] = permission.name.split(' ');
        const key = group || 'general';
        if (!groups[key]) {
            groups[key] = [];
        }
        groups[key].push(permission);
        return groups;
    }, {});
});

const allPermissionNames = computed(() => props.permissions.map(permission => permission.name));
const isAllSelected = computed(() => form.permissions.length === allPermissionNames.value.length);

const toggleAll = () => {
    if (isAllSelected.value) {
        form.permissions = [];
    } else {
        form.permissions = [...allPermissionNames.value];
    }
};

const toggleGroup = (group) => {
    const names = groupedPermissions.value[group].map(permission => permission.name);
    const hasAll = names.every(name => form.permissions.includes(name));

    form.permissions = hasAll
        ? form.permissions.filter(name => !names.includes(name))
        : Array.from(new Set([...form.permissions, ...names]));
};

const isGroupSelected = (group) => {
    const names = groupedPermissions.value[group].map(permission => permission.name);
    return names.every(name => form.permissions.includes(name));
};

const submit = () => {
    form.put(route('roles.update', props.role.id));
};

const deleteForm = useForm({});
const destroy = () => {
    if (confirm(`Delete role "${props.role.name}"?`)) {
        deleteForm.delete(route('roles.destroy', props.role.id));
    }
};
</script>
