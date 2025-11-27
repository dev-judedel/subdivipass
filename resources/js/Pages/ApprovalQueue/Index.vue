<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Approval Queue</h1>
                    <p class="mt-1 text-sm text-gray-500">Review and approve pending pass requests</p>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Total Pending -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Pending</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_pending || 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Urgent Requests -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Urgent (Valid Tomorrow)</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.urgent || 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Today's Requests -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Requests</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.today_requests || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quick Search</label>
                        <input
                            v-model="filterForm.search"
                            type="text"
                            placeholder="Pass #, Name, Contact..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @input="debouncedFilter"
                        />
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

            <!-- Batch Actions Bar (shown when passes are selected) -->
            <div v-if="selectedPassIds.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-blue-900">
                            {{ selectedPassIds.length }} pass{{ selectedPassIds.length > 1 ? 'es' : '' }} selected
                        </span>
                        <button
                            @click="clearSelection"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                        >
                            Clear selection
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            @click="batchApprove"
                            :disabled="batchApproveForm.processing"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ batchApproveForm.processing ? 'Approving...' : 'Approve Selected' }}
                        </button>
                        <button
                            @click="openBatchRejectModal"
                            :disabled="batchRejectForm.processing"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ batchRejectForm.processing ? 'Rejecting...' : 'Reject Selected' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Passes Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div v-if="passes.data.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        @change="toggleSelectAll"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pass Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitor Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subdivision</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pass Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid From/To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="pass in passes.data"
                                :key="pass.id"
                                :class="[
                                    'hover:bg-gray-50 transition',
                                    isUrgent(pass) ? 'bg-red-50' : ''
                                ]"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        :checked="selectedPassIds.includes(pass.id)"
                                        @change="togglePassSelection(pass.id)"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ pass.pass_number }}</div>
                                            <div class="text-xs text-gray-500">PIN: {{ pass.pin }}</div>
                                        </div>
                                        <span
                                            v-if="isUrgent(pass)"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800"
                                            title="Urgent - Valid from tomorrow or earlier"
                                        >
                                            Urgent
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ pass.requester?.name || 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ pass.requester?.email || '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">{{ pass.visitor_name }}</div>
                                            <div class="text-xs text-gray-500">{{ pass.visitor_contact || 'No contact' }}</div>
                                        </div>
                                        <!-- Worker Pass Badge -->
                                        <span
                                            v-if="pass.pass_mode === 'group'"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800"
                                            :title="`Worker Pass - ${pass.group_size || 0} workers`"
                                        >
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            {{ pass.group_size || 0 }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ pass.subdivision?.name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ pass.type?.name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(pass.valid_from) }}</div>
                                    <div class="text-xs text-gray-500">to {{ formatDate(pass.valid_to) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(pass.created_at) }}</div>
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

                                        <!-- Approve Button -->
                                        <button
                                            @click="approveSinglePass(pass)"
                                            class="text-green-600 hover:text-green-900"
                                            title="Approve Pass"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>

                                        <!-- Reject Button -->
                                        <button
                                            @click="openSingleRejectModal(pass)"
                                            class="text-red-600 hover:text-red-900"
                                            title="Reject Pass"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No pending passes</h3>
                    <p class="mt-1 text-sm text-gray-500">All passes have been reviewed. Great job!</p>
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

        <!-- Batch Reject Modal -->
        <div v-if="showBatchRejectModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeBatchRejectModal"></div>
                <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Reject Selected Passes</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    You are about to reject {{ selectedPassIds.length }} pass{{ selectedPassIds.length > 1 ? 'es' : '' }}.
                                    Please provide a reason for rejection.
                                </p>
                            </div>
                            <div class="mt-4">
                                <textarea
                                    v-model="batchRejectForm.reason"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Enter rejection reason..."
                                    required
                                ></textarea>
                                <p v-if="batchRejectForm.errors.reason" class="mt-1 text-sm text-red-600">{{ batchRejectForm.errors.reason }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button
                            @click="confirmBatchReject"
                            :disabled="batchRejectForm.processing || !batchRejectForm.reason"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ batchRejectForm.processing ? 'Rejecting...' : 'Reject Passes' }}
                        </button>
                        <button
                            @click="closeBatchRejectModal"
                            :disabled="batchRejectForm.processing"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm disabled:opacity-50"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Single Pass Reject Modal -->
        <div v-if="showSingleRejectModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeSingleRejectModal"></div>
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
                                    v-model="singleRejectForm.reason"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Enter rejection reason..."
                                    required
                                ></textarea>
                                <p v-if="singleRejectForm.errors.reason" class="mt-1 text-sm text-red-600">{{ singleRejectForm.errors.reason }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button
                            @click="confirmSingleReject"
                            :disabled="singleRejectForm.processing || !singleRejectForm.reason"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ singleRejectForm.processing ? 'Rejecting...' : 'Reject Pass' }}
                        </button>
                        <button
                            @click="closeSingleRejectModal"
                            :disabled="singleRejectForm.processing"
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
import { Link, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

// Props
const props = defineProps({
    passes: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({
            total_pending: 0,
            urgent: 0,
            today_requests: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    subdivisions: {
        type: Array,
        default: () => [],
    },
    passTypes: {
        type: Array,
        default: () => [],
    },
});

// State
const selectedPassIds = ref([]);
const showBatchRejectModal = ref(false);
const showSingleRejectModal = ref(false);
const selectedSinglePass = ref(null);

// Filter form
const filterForm = ref({
    search: props.filters.search || '',
    subdivision_id: props.filters.subdivision_id || '',
    pass_type_id: props.filters.pass_type_id || '',
});

// Forms
const batchApproveForm = useForm({
    pass_ids: [],
});

const batchRejectForm = useForm({
    pass_ids: [],
    reason: '',
});

const singleRejectForm = useForm({
    reason: '',
});

// Computed
const allSelected = computed(() => {
    return props.passes.data.length > 0 &&
           selectedPassIds.value.length === props.passes.data.length;
});

const hasActiveFilters = computed(() => {
    return filterForm.value.search ||
           filterForm.value.subdivision_id ||
           filterForm.value.pass_type_id;
});

// Methods
const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedPassIds.value = [];
    } else {
        selectedPassIds.value = props.passes.data.map(pass => pass.id);
    }
};

const togglePassSelection = (passId) => {
    const index = selectedPassIds.value.indexOf(passId);
    if (index > -1) {
        selectedPassIds.value.splice(index, 1);
    } else {
        selectedPassIds.value.push(passId);
    }
};

const clearSelection = () => {
    selectedPassIds.value = [];
};

const applyFilters = () => {
    router.get(route('approval-queue.index'), filterForm.value, {
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
        subdivision_id: '',
        pass_type_id: '',
    };
    applyFilters();
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

const isUrgent = (pass) => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(23, 59, 59, 999);

    const validFrom = new Date(pass.valid_from);
    return validFrom <= tomorrow;
};

// Batch Actions
const batchApprove = () => {
    if (selectedPassIds.value.length === 0) return;

    if (confirm(`Are you sure you want to approve ${selectedPassIds.value.length} pass${selectedPassIds.value.length > 1 ? 'es' : ''}?`)) {
        batchApproveForm.pass_ids = selectedPassIds.value;
        batchApproveForm.post(route('approval-queue.batch-approve'), {
            preserveScroll: true,
            onSuccess: () => {
                selectedPassIds.value = [];
                batchApproveForm.reset();
            },
        });
    }
};

const openBatchRejectModal = () => {
    if (selectedPassIds.value.length === 0) return;
    batchRejectForm.reason = '';
    batchRejectForm.errors = {};
    showBatchRejectModal.value = true;
};

const closeBatchRejectModal = () => {
    showBatchRejectModal.value = false;
    batchRejectForm.reset();
};

const confirmBatchReject = () => {
    batchRejectForm.pass_ids = selectedPassIds.value;
    batchRejectForm.post(route('approval-queue.batch-reject'), {
        preserveScroll: true,
        onSuccess: () => {
            selectedPassIds.value = [];
            closeBatchRejectModal();
        },
    });
};

// Single Pass Actions
const approveSinglePass = (pass) => {
    if (confirm('Are you sure you want to approve this pass?')) {
        router.post(route('passes.approve', pass.id), {}, {
            preserveScroll: true,
        });
    }
};

const openSingleRejectModal = (pass) => {
    selectedSinglePass.value = pass;
    singleRejectForm.reason = '';
    singleRejectForm.errors = {};
    showSingleRejectModal.value = true;
};

const closeSingleRejectModal = () => {
    showSingleRejectModal.value = false;
    selectedSinglePass.value = null;
    singleRejectForm.reset();
};

const confirmSingleReject = () => {
    singleRejectForm.post(route('passes.reject', selectedSinglePass.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeSingleRejectModal();
        },
    });
};
</script>
