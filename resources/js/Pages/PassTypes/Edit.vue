<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center">
                    <Link
                        :href="route('pass-types.index')"
                        class="mr-4 text-gray-600 hover:text-gray-900"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Pass Type</h1>
                        <p class="mt-1 text-sm text-gray-500">Update pass type configuration</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Subdivision -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Subdivision <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.subdivision_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.subdivision_id }"
                            >
                                <option value="">Select Subdivision</option>
                                <option v-for="subdivision in subdivisions" :key="subdivision.id" :value="subdivision.id">
                                    {{ subdivision.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.subdivision_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.subdivision_id }}
                            </p>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Visitor, Delivery, Job Order"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Slug -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Slug <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.slug"
                                type="text"
                                placeholder="e.g., visitor, delivery"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.slug }"
                            />
                            <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, and dashes only</p>
                            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">
                                {{ form.errors.slug }}
                            </p>
                        </div>

                        <!-- Color -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Color <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center space-x-2">
                                <input
                                    v-model="form.color"
                                    type="color"
                                    class="h-10 w-20 rounded border-gray-300"
                                />
                                <input
                                    v-model="form.color"
                                    type="text"
                                    placeholder="#3B82F6"
                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.color }"
                                />
                            </div>
                            <p v-if="form.errors.color" class="mt-1 text-sm text-red-600">
                                {{ form.errors.color }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Brief description of this pass type..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.description }"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Validity Settings -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Validity Settings</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Default Validity Hours -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Default Validity (hours) <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model.number="form.default_validity_hours"
                                type="number"
                                min="1"
                                max="8760"
                                placeholder="24"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.default_validity_hours }"
                            />
                            <p class="mt-1 text-xs text-gray-500">Default duration when creating a pass</p>
                            <p v-if="form.errors.default_validity_hours" class="mt-1 text-sm text-red-600">
                                {{ form.errors.default_validity_hours }}
                            </p>
                        </div>

                        <!-- Max Validity Hours -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Maximum Validity (hours)
                            </label>
                            <input
                                v-model.number="form.max_validity_hours"
                                type="number"
                                min="1"
                                max="8760"
                                placeholder="168 (optional)"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.max_validity_hours }"
                            />
                            <p class="mt-1 text-xs text-gray-500">Maximum allowed duration (leave empty for no limit)</p>
                            <p v-if="form.errors.max_validity_hours" class="mt-1 text-sm text-red-600">
                                {{ form.errors.max_validity_hours }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Approval & Status -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Approval & Status</h2>

                    <div class="space-y-4">
                        <!-- Requires Approval -->
                        <div class="flex items-center">
                            <input
                                v-model="form.requires_approval"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label class="ml-3 text-sm font-medium text-gray-700">
                                Requires approval before activation
                            </label>
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label class="ml-3 text-sm font-medium text-gray-700">
                                Active (available for use)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <Link
                        :href="route('pass-types.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Pass Type</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    passType: Object,
    subdivisions: Array,
});

const form = useForm({
    subdivision_id: props.passType.subdivision_id,
    name: props.passType.name,
    slug: props.passType.slug,
    description: props.passType.description || '',
    color: props.passType.color,
    default_validity_hours: props.passType.default_validity_hours,
    max_validity_hours: props.passType.max_validity_hours,
    requires_approval: props.passType.requires_approval,
    is_active: props.passType.is_active,
});

const submit = () => {
    form.put(route('pass-types.update', props.passType.id), {
        preserveScroll: true,
    });
};
</script>
