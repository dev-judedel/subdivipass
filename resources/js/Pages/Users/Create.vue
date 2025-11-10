<template>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white shadow">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Administration</p>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">Create User</h1>
                    <p class="mt-1 text-sm text-gray-500">Provision access, assign roles, and configure guard coverage.</p>
                </div>
                <Link
                    :href="route('users.index')"
                    class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold"
                >
                    Back to list
                </Link>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="At least 8 characters"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>

                            <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">Confirm Password</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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

                <div class="flex items-center justify-end gap-3 pt-4">
                    <Link
                        :href="route('users.index')"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : 'Create User' }}
                    </button>
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
    roles: { type: Array, default: () => [] },
    subdivisions: { type: Array, default: () => [] },
    gates: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    role: '',
    subdivision_ids: [],
    primary_subdivision_id: '',
    gate_ids: [],
    password: '',
    password_confirmation: '',
    status: 'active',
    two_factor_enabled: false,
});

const submit = () => {
    form.post(route('users.store'));
};
</script>
