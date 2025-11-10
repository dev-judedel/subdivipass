<template>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white shadow">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Administration</p>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">Edit User</h1>
                    <p class="mt-1 text-sm text-gray-500">Update user details, assignments, and security settings.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('users.show', user.id)"
                        class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold"
                    >
                        View profile
                    </Link>
                    <Link
                        :href="route('users.index')"
                        class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold"
                    >
                        Back to list
                    </Link>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <form class="bg-white rounded-lg shadow-sm p-6 space-y-8" @submit.prevent="submit">
                <section>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input
                                v-model="form.phone"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select
                                v-model="form.role"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Select a role</option>
                                <option
                                    v-for="role in roles"
                                    :key="role.id"
                                    :value="role.name"
                                >
                                    {{ role.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Security</h2>
                            <p class="text-sm text-gray-500 mb-3">Leave password fields blank to keep the existing password.</p>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Optional"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>

                            <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">Confirm Password</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Optional"
                            />
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Status</label>
                                <select
                                    v-model="form.status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                            <label class="inline-flex items-center space-x-3">
                                <input
                                    v-model="form.two_factor_enabled"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-700">Require 2FA at login</span>
                            </label>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Assignments</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primary Subdivision</label>
                            <select
                                v-model="form.primary_subdivision_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Select primary subdivision</option>
                                <option
                                    v-for="subdivision in subdivisions"
                                    :key="subdivision.id"
                                    :value="subdivision.id"
                                >
                                    {{ subdivision.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.primary_subdivision_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.primary_subdivision_id }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Subdivisions</label>
                            <select
                                v-model="form.subdivision_ids"
                                multiple
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent min-h-[120px]"
                            >
                                <option
                                    v-for="subdivision in subdivisions"
                                    :key="subdivision.id"
                                    :value="subdivision.id"
                                >
                                    {{ subdivision.name }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple subdivisions.</p>
                            <p v-if="form.errors.subdivision_ids" class="mt-1 text-sm text-red-600">
                                {{ form.errors.subdivision_ids }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Gates (for guards)</label>
                            <select
                                v-model="form.gate_ids"
                                multiple
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent min-h-[120px]"
                            >
                                <option
                                    v-for="gate in gates"
                                    :key="gate.id"
                                    :value="gate.id"
                                >
                                    {{ gate.name }} — {{ gate.subdivision?.name }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Leave empty if not applicable.</p>
                            <p v-if="form.errors.gate_ids" class="mt-1 text-sm text-red-600">{{ form.errors.gate_ids }}</p>
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-between pt-4">
                    <button
                        type="button"
                        class="text-sm text-red-600 hover:text-red-700 font-semibold"
                        @click="destroy"
                    >
                        Delete user
                    </button>
                    <div class="flex gap-3">
                        <Link
                            :href="route('users.show', user.id)"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    user: { type: Object, required: true },
    roles: { type: Array, default: () => [] },
    subdivisions: { type: Array, default: () => [] },
    gates: { type: Array, default: () => [] },
});

const normalizedSubdivisions = Array.isArray(props.user.subdivision_ids) ? props.user.subdivision_ids : [];
const normalizedGates = Array.isArray(props.user.gate_ids) ? props.user.gate_ids : [];

const form = useForm({
    name: props.user.name ?? '',
    email: props.user.email ?? '',
    phone: props.user.phone ?? '',
    role: props.user.roles?.[0]?.name ?? '',
    subdivision_ids: [...normalizedSubdivisions],
    primary_subdivision_id: props.user.primary_subdivision_id ?? '',
    gate_ids: [...normalizedGates],
    password: '',
    password_confirmation: '',
    status: props.user.status ?? 'active',
    two_factor_enabled: Boolean(props.user.two_factor_enabled),
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};

const deleteForm = useForm({});
const destroy = () => {
    if (confirm('Are you sure you want to delete this user?')) {
        deleteForm.delete(route('users.destroy', props.user.id));
    }
};
</script>
