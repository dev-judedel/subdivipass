<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Pass Type Management</h1>
                        <p class="mt-1 text-sm text-gray-500">Configure and manage different types of passes</p>
                    </div>
                    <Link
                        :href="route('pass-types.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition duration-150"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Pass Type
                    </Link>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="Search pass types..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Subdivision Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subdivision</label>
                        <select
                            v-model="searchForm.subdivision_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All Subdivisions</option>
                            <option v-for="subdivision in subdivisions" :key="subdivision.id" :value="subdivision.id">
                                {{ subdivision.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select
                            v-model="searchForm.is_active"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <!-- Approval Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Approval</label>
                        <select
                            v-model="searchForm.requires_approval"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All</option>
                            <option value="1">Required</option>
                            <option value="0">Not Required</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pass Types List -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subdivision
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Validity
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Approval
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="passType in passTypes.data" :key="passType.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="w-3 h-3 rounded-full mr-3"
                                        :style="{ backgroundColor: passType.color }"
                                    ></div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ passType.name }}</div>
                                        <div class="text-sm text-gray-500">{{ passType.slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ passType.subdivision?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ passType.default_validity_hours }}h default</div>
                                <div v-if="passType.max_validity_hours" class="text-xs text-gray-400">
                                    Max: {{ passType.max_validity_hours }}h
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="passType.requires_approval"
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"
                                >
                                    Required
                                </span>
                                <span
                                    v-else
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                                >
                                    Auto
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="passType.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ passType.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <Link
                                        :href="route('pass-types.edit', passType.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Edit"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="toggleStatus(passType)"
                                        class="text-gray-600 hover:text-gray-900"
                                        :title="passType.is_active ? 'Deactivate' : 'Activate'"
                                    >
                                        <svg v-if="passType.is_active" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="confirmDelete(passType)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="passTypes.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ passTypes.from }} to {{ passTypes.to }} of {{ passTypes.total }} pass types
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-for="link in passTypes.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1 text-sm rounded',
                                    link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    passTypes: Object,
    subdivisions: Array,
    filters: Object,
});

// Search form
const searchForm = reactive({
    search: props.filters.search || '',
    subdivision_id: props.filters.subdivision_id || '',
    is_active: props.filters.is_active || '',
    requires_approval: props.filters.requires_approval || '',
});

// Debounced search
let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

// Apply filters
const applyFilters = () => {
    router.get(route('pass-types.index'), searchForm, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Toggle status
const toggleStatus = (passType) => {
    if (confirm(`Are you sure you want to ${passType.is_active ? 'deactivate' : 'activate'} this pass type?`)) {
        router.post(route('pass-types.change-status', passType.id), {
            is_active: !passType.is_active,
        }, {
            preserveScroll: true,
        });
    }
};

// Delete confirmation
const confirmDelete = (passType) => {
    if (confirm(`Are you sure you want to delete "${passType.name}"? This action cannot be undone.`)) {
        router.delete(route('pass-types.destroy', passType.id), {
            preserveScroll: true,
        });
    }
};
</script>
