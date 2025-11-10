<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Pass Management</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage and track all subdivision passes</p>
                    </div>
                    <Link
                        :href="route('passes.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create New Pass
                    </Link>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            v-model="filterForm.search"
                            type="text"
                            placeholder="Pass #, Name, Contact, PIN..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @input="debouncedFilter"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select
                            v-model="filterForm.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                            <option value="revoked">Revoked</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <!-- Subdivision Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subdivision</label>
                        <select
                            v-model="filterForm.subdivision_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All Subdivisions</option>
                            <option
                                v-for="subdivision in subdivisions"
                                :key="subdivision.id"
                                :value="subdivision.id"
                            >
                                {{ subdivision.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Pass Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pass Type</label>
                        <select
                            v-model="filterForm.pass_type_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All Types</option>
                            <option
                                v-for="type in passTypes"
                                :key="type.id"
                                :value="type.id"
                            >
                                {{ type.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div v-if="hasActiveFilters" class="mt-4 flex items-center gap-2">
                    <span class="text-sm text-gray-600">Active filters:</span>
                    <button
                        v-if="filterForm.status"
                        @click="clearFilter('status')"
                        class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm"
                    >
                        Status: {{ filterForm.status }}
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <button
                        v-if="filterForm.search"
                        @click="clearFilter('search')"
                        class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm"
                    >
                        Search: {{ filterForm.search }}
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <button
                        @click="clearAllFilters"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                    >
                        Clear all
                    </button>
                </div>
            </div>

            <!-- Passes Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div v-if="passes.data.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pass Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subdivision</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="pass in passes.data"
                                :key="pass.id"
                                class="hover:bg-gray-50 transition"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ pass.pass_number }}</div>
                                    <div class="text-xs text-gray-500">PIN: {{ pass.pin }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ pass.visitor_name }}</div>
                                    <div class="text-xs text-gray-500">{{ pass.visitor_contact || 'No contact' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ pass.type?.name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ pass.subdivision?.name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(pass.valid_from) }}</div>
                                    <div class="text-xs text-gray-500">to {{ formatDate(pass.valid_to) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusBadgeClass(pass.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ pass.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <!-- View Button -->
                                        <Link
                                            :href="route('passes.show', pass.id)"
                                            class="text-blue-600 hover:text-blue-900"
                                            title="View Details"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </Link>

                                        <!-- Edit Button -->
                                        <Link
                                            v-if="canEdit(pass)"
                                            :href="route('passes.edit', pass.id)"
                                            class="text-yellow-600 hover:text-yellow-900"
                                            title="Edit Pass"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </Link>

                                        <!-- Approve Button -->
                                        <button
                                            v-if="canApprove(pass)"
                                            @click="approvePass(pass)"
                                            class="text-green-600 hover:text-green-900"
                                            title="Approve Pass"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>

                                        <!-- Reject Button -->
                                        <button
                                            v-if="canReject(pass)"
                                            @click="openRejectModal(pass)"
                                            class="text-red-600 hover:text-red-900"
                                            title="Reject Pass"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>

                                        <!-- Revoke Button -->
                                        <button
                                            v-if="canRevoke(pass)"
                                            @click="openRevokeModal(pass)"
                                            class="text-orange-600 hover:text-orange-900"
                                            title="Revoke Pass"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            </svg>
                                        </button>

                                        <!-- Download QR Button -->
                                        <a
                                            v-if="pass.qr_code_path"
                                            :href="route('passes.qr.download', pass.id)"
                                            class="text-purple-600 hover:text-purple-900"
                                            title="Download QR Code"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No passes found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new pass.</p>
                    <div class="mt-6">
                        <Link
                            :href="route('passes.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create New Pass
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="passes.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <a
                                v-if="passes.prev_page_url"
                                :href="passes.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </a>
                            <a
                                v-if="passes.next_page_url"
                                :href="passes.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </a>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ passes.from }}</span>
                                    to
                                    <span class="font-medium">{{ passes.to }}</span>
                                    of
                                    <span class="font-medium">{{ passes.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <a
                                        v-for="link in passes.links"
                                        :key="link.label"
                                        :href="link.url"
                                        :class="[
                                            link.active
                                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                        ]"
                                        v-html="link.label"
                                    ></a>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeRejectModal"></div>
                <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Reject Pass</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Please provide a reason for rejecting this pass.
                                </p>
                            </div>
                            <div class="mt-4">
                                <textarea
                                    v-model="rejectForm.reason"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Enter rejection reason..."
                                ></textarea>
                                <p v-if="rejectForm.errors.reason" class="mt-1 text-sm text-red-600">{{ rejectForm.errors.reason }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button
                            @click="confirmReject"
                            :disabled="rejectForm.processing"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm disabled:opacity-50"
                        >
                            {{ rejectForm.processing ? 'Rejecting...' : 'Reject Pass' }}
                        </button>
                        <button
                            @click="closeRejectModal"
                            :disabled="rejectForm.processing"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm disabled:opacity-50"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revoke Modal -->
        <div v-if="showRevokeModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeRevokeModal"></div>
                <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Revoke Pass</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to revoke this pass? You can optionally provide a reason.
                                </p>
                            </div>
                            <div class="mt-4">
                                <textarea
                                    v-model="revokeForm.reason"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    placeholder="Enter revocation reason (optional)..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button
                            @click="confirmRevoke"
                            :disabled="revokeForm.processing"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:col-start-2 sm:text-sm disabled:opacity-50"
                        >
                            {{ revokeForm.processing ? 'Revoking...' : 'Revoke Pass' }}
                        </button>
                        <button
                            @click="closeRevokeModal"
                            :disabled="revokeForm.processing"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm disabled:opacity-50"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';

// Props
const props = defineProps({
    passes: Object,
    subdivisions: Array,
    passTypes: Array,
    filters: Object,
});

// Page data
const page = usePage();
const user = computed(() => page.props.auth?.user);

// Filter form
const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    subdivision_id: props.filters.subdivision_id || '',
    pass_type_id: props.filters.pass_type_id || '',
});

// Reject modal
const showRejectModal = ref(false);
const selectedPass = ref(null);
const rejectForm = useForm({
    reason: '',
});

// Revoke modal
const showRevokeModal = ref(false);
const revokeForm = useForm({
    reason: '',
});

// Computed
const hasActiveFilters = computed(() => {
    return filterForm.value.search ||
           filterForm.value.status ||
           filterForm.value.subdivision_id ||
           filterForm.value.pass_type_id;
});

// Methods
const applyFilters = () => {
    router.get(route('passes.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

let debounceTimer;
const debouncedFilter = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        applyFilters();
    }, 500);
};

const clearFilter = (filterName) => {
    filterForm.value[filterName] = '';
    applyFilters();
};

const clearAllFilters = () => {
    filterForm.value = {
        search: '',
        status: '',
        subdivision_id: '',
        pass_type_id: '',
    };
    applyFilters();
};

const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        active: 'bg-green-100 text-green-800',
        expired: 'bg-red-100 text-red-800',
        revoked: 'bg-orange-100 text-orange-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const canEdit = (pass) => {
    return ['draft', 'pending'].includes(pass.status);
};

const canApprove = (pass) => {
    const hasRole = user.value?.roles?.some(role => ['admin', 'super-admin'].includes(role.name));
    return hasRole && pass.status === 'pending';
};

const canReject = (pass) => {
    const hasRole = user.value?.roles?.some(role => ['admin', 'super-admin'].includes(role.name));
    return hasRole && pass.status === 'pending';
};

const canRevoke = (pass) => {
    return ['approved', 'active'].includes(pass.status);
};

const approvePass = (pass) => {
    if (confirm('Are you sure you want to approve this pass?')) {
        router.post(route('passes.approve', pass.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Success message will be shown via flash
            },
        });
    }
};

const openRejectModal = (pass) => {
    selectedPass.value = pass;
    rejectForm.reason = '';
    rejectForm.errors = {};
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    selectedPass.value = null;
    rejectForm.reset();
};

const confirmReject = () => {
    rejectForm.post(route('passes.reject', selectedPass.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeRejectModal();
        },
    });
};

const openRevokeModal = (pass) => {
    selectedPass.value = pass;
    revokeForm.reason = '';
    showRevokeModal.value = true;
};

const closeRevokeModal = () => {
    showRevokeModal.value = false;
    selectedPass.value = null;
    revokeForm.reset();
};

const confirmRevoke = () => {
    revokeForm.post(route('passes.revoke', selectedPass.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeRevokeModal();
        },
    });
};
</script>
