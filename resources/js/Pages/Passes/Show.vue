<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold text-gray-900">{{ pass.pass_number }}</h1>
                            <span :class="getStatusBadgeClass(pass.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
                                {{ pass.status?.toUpperCase() }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Pass Details</p>
                    </div>
                    <Link
                        :href="route('passes.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to List
                    </Link>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - QR Code and Actions -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- QR Code Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">QR Code</h2>
                        <div v-if="qrCodeUrl" class="text-center">
                            <img :src="qrCodeUrl" alt="QR Code" class="w-full max-w-sm mx-auto rounded-lg border-2 border-gray-200" />
                            <p class="mt-3 text-xs text-gray-500">Scan this QR code at the gate</p>
                        </div>
                        <div v-else class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">QR code not generated</p>
                        </div>
                    </div>

                    <!-- PIN Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Backup PIN</h2>
                        <div class="text-center">
                            <div class="text-4xl font-mono font-bold text-blue-600 tracking-wider bg-blue-50 py-4 rounded-lg">
                                {{ pass.pin }}
                            </div>
                            <p class="mt-3 text-xs text-gray-500">Use this PIN for manual validation</p>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                        <div class="space-y-3">
                            <!-- Download QR -->
                            <a
                                v-if="qrCodeUrl"
                                :href="route('passes.qr.download', pass.id)"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download QR Code
                            </a>

                            <!-- Edit -->
                            <Link
                                v-if="canEdit"
                                :href="route('passes.edit', pass.id)"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Pass
                            </Link>

                            <!-- Approve -->
                            <button
                                v-if="canApprove"
                                @click="approvePass"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Approve Pass
                            </button>

                            <!-- Reject -->
                            <button
                                v-if="canReject"
                                @click="openRejectModal"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Reject Pass
                            </button>

                            <!-- Revoke -->
                            <button
                                v-if="canRevoke"
                                @click="openRevokeModal"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                                Revoke Pass
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Pass Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Pass Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Pass Information</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Pass Number</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ pass.pass_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Pass Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.type?.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Subdivision</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.subdivision?.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span :class="getStatusBadgeClass(pass.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ pass.status }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Valid From</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(pass.valid_from) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Valid To</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(pass.valid_to) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Worker List (Worker Pass) - ENHANCED VERSION -->
                    <div v-if="pass.pass_mode === 'group' && pass.workers && pass.workers.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Workers</h2>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-500">
                                    {{ admittedWorkersCount }} / {{ pass.workers.length }} admitted
                                </span>
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ pass.workers.length }} Worker{{ pass.workers.length !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div
                                v-for="worker in pass.workers"
                                :key="worker.id"
                                class="border border-gray-200 rounded-lg overflow-hidden hover:border-purple-300 transition"
                            >
                                <!-- Worker Card Header -->
                                <div class="p-4 bg-gradient-to-r from-gray-50 to-white">
                                    <div class="flex items-start gap-4">
                                        <!-- Worker Photo -->
                                        <div class="flex-shrink-0">
                                            <div
                                                v-if="worker.photo_url"
                                                class="w-20 h-20 rounded-lg overflow-hidden border-2"
                                                :class="worker.is_admitted ? 'border-green-400' : 'border-gray-200'"
                                            >
                                                <img :src="worker.photo_url" :alt="worker.worker_name" class="w-full h-full object-cover" />
                                            </div>
                                            <div
                                                v-else
                                                class="w-20 h-20 rounded-lg bg-gray-100 border-2 flex items-center justify-center"
                                                :class="worker.is_admitted ? 'border-green-400' : 'border-gray-200'"
                                            >
                                                <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Worker Details -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-2">
                                                <h3 class="text-base font-semibold text-gray-900">{{ worker.worker_name }}</h3>
                                                <span
                                                    v-if="worker.is_admitted"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800"
                                                >
                                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Inside
                                                </span>
                                                <span
                                                    :class="worker.status === 'active' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold"
                                                >
                                                    {{ worker.status }}
                                                </span>
                                            </div>
                                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                                <div v-if="worker.worker_position">
                                                    <dt class="text-xs text-gray-500">Position</dt>
                                                    <dd class="text-gray-900">{{ worker.worker_position }}</dd>
                                                </div>
                                                <div v-if="worker.worker_id_number">
                                                    <dt class="text-xs text-gray-500">ID Number</dt>
                                                    <dd class="text-gray-900 font-mono">{{ worker.worker_id_number }}</dd>
                                                </div>
                                                <div v-if="worker.worker_contact">
                                                    <dt class="text-xs text-gray-500">Contact</dt>
                                                    <dd class="text-gray-900">{{ worker.worker_contact }}</dd>
                                                </div>
                                                <div v-if="worker.worker_email">
                                                    <dt class="text-xs text-gray-500">Email</dt>
                                                    <dd class="text-gray-900 truncate">{{ worker.worker_email }}</dd>
                                                </div>
                                                <div v-if="worker.last_scan_at">
                                                    <dt class="text-xs text-gray-500">Last Scan</dt>
                                                    <dd class="text-gray-900">{{ formatDate(worker.last_scan_at) }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-xs text-gray-500">Total Scans</dt>
                                                    <dd class="text-gray-900">{{ worker.total_scans || 0 }}</dd>
                                                </div>
                                            </dl>
                                        </div>

                                        <!-- QR Code Preview -->
                                        <div v-if="worker.qr_code_url" class="flex-shrink-0">
                                            <button
                                                @click="showWorkerQR(worker)"
                                                class="group relative w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200 hover:border-purple-400 transition"
                                            >
                                                <img :src="worker.qr_code_url" alt="Worker QR" class="w-full h-full object-cover" />
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-white opacity-0 group-hover:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                    </svg>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Expandable Scan History -->
                                <div v-if="workerHasScans(worker)" class="border-t border-gray-200">
                                    <button
                                        @click="toggleWorkerHistory(worker.id)"
                                        class="w-full px-4 py-2 flex items-center justify-between text-sm text-gray-600 hover:bg-gray-50 transition"
                                    >
                                        <span class="flex items-center gap-2">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            View Scan History ({{ getWorkerScanCount(worker) }})
                                        </span>
                                        <svg
                                            class="h-5 w-5 transition-transform"
                                            :class="{ 'rotate-180': expandedWorkers.has(worker.id) }"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Scan History Timeline -->
                                    <div
                                        v-show="expandedWorkers.has(worker.id)"
                                        class="px-4 py-3 bg-gray-50 border-t border-gray-200"
                                    >
                                        <div class="flow-root">
                                            <ul class="-mb-6">
                                                <li
                                                    v-for="(scan, index) in getWorkerScans(worker)"
                                                    :key="index"
                                                    class="relative pb-6"
                                                >
                                                    <span
                                                        v-if="index !== getWorkerScans(worker).length - 1"
                                                        class="absolute top-4 left-3 -ml-px h-full w-0.5 bg-gray-300"
                                                    ></span>
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            <span
                                                                :class="scan.scan_type === 'entry' ? 'bg-green-500' : 'bg-blue-500'"
                                                                class="h-6 w-6 rounded-full flex items-center justify-center ring-4 ring-white"
                                                            >
                                                                <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                        v-if="scan.scan_type === 'entry'"
                                                                        fill-rule="evenodd"
                                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                        clip-rule="evenodd"
                                                                    />
                                                                    <path
                                                                        v-else
                                                                        fill-rule="evenodd"
                                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                                        clip-rule="evenodd"
                                                                    />
                                                                </svg>
                                                            </span>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="text-xs">
                                                                <span class="font-medium text-gray-900">{{ scan.scan_type === 'entry' ? 'Entry' : 'Exit' }}</span>
                                                                at
                                                                <span class="font-medium text-gray-900">{{ scan.gate?.name || 'Unknown Gate' }}</span>
                                                            </div>
                                                            <p class="mt-0.5 text-xs text-gray-500">
                                                                {{ formatDate(scan.scanned_at) }}
                                                                <span v-if="scan.scanned_by">• By {{ scan.scanned_by.name }}</span>
                                                            </p>
                                                            <p v-if="scan.result_message" class="mt-1 text-xs text-gray-600">
                                                                {{ scan.result_message }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visitor Information (Single Pass) -->
                    <div v-if="pass.pass_mode === 'single'" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Visitor Information</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.visitor_name }}</dd>
                            </div>
                            <div v-if="pass.visitor_contact">
                                <dt class="text-sm font-medium text-gray-500">Contact Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.visitor_contact }}</dd>
                            </div>
                            <div v-if="pass.visitor_email">
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.visitor_email }}</dd>
                            </div>
                            <div v-if="pass.visitor_company">
                                <dt class="text-sm font-medium text-gray-500">Company</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.visitor_company }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Vehicle Information (Single Pass Only) -->
                    <div v-if="pass.pass_mode === 'single' && (pass.vehicle_plate || pass.vehicle_model)" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Vehicle Information</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-if="pass.vehicle_plate">
                                <dt class="text-sm font-medium text-gray-500">Plate Number</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ pass.vehicle_plate }}</dd>
                            </div>
                            <div v-if="pass.vehicle_model">
                                <dt class="text-sm font-medium text-gray-500">Vehicle Model</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.vehicle_model }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Visit Details -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Visit Details</h2>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.purpose }}</dd>
                            </div>
                            <div v-if="pass.destination">
                                <dt class="text-sm font-medium text-gray-500">Destination</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.destination }}</dd>
                            </div>
                            <div v-if="pass.notes">
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.notes }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Approval Information -->
                    <div v-if="pass.approver || pass.rejection_reason" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Approval Information</h2>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Requested By</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.requester?.name }}</dd>
                            </div>
                            <div v-if="pass.approver">
                                <dt class="text-sm font-medium text-gray-500">Approved By</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ pass.approver?.name }}</dd>
                            </div>
                            <div v-if="pass.approved_at">
                                <dt class="text-sm font-medium text-gray-500">Approved At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(pass.approved_at) }}</dd>
                            </div>
                            <div v-if="pass.rejection_reason">
                                <dt class="text-sm font-medium text-gray-500">Rejection Reason</dt>
                                <dd class="mt-1 text-sm text-red-600">{{ pass.rejection_reason }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Scan History -->
                    <div v-if="pass.scans && pass.scans.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Scan History</h2>
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li v-for="(scan, index) in pass.scans" :key="scan.id" class="relative pb-8">
                                    <span v-if="index !== pass.scans.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span :class="getScanTypeClass(scan.scan_type)" class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <span class="font-medium text-gray-900">{{ scan.scan_type }}</span> at
                                                    <span class="font-medium text-gray-900">{{ scan.gate?.name }}</span>
                                                </p>
                                                <p class="mt-0.5 text-xs text-gray-500">
                                                    By {{ scan.scanned_by?.name }} • {{ formatDate(scan.scanned_at) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <dt class="text-sm font-medium text-blue-600">Scan Count</dt>
                                <dd class="mt-1 text-2xl font-bold text-blue-900">{{ pass.scan_count || 0 }}</dd>
                            </div>
                            <div v-if="pass.last_scanned_at" class="bg-green-50 rounded-lg p-4">
                                <dt class="text-sm font-medium text-green-600">Last Scanned</dt>
                                <dd class="mt-1 text-sm font-medium text-green-900">{{ formatDate(pass.last_scanned_at) }}</dd>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-4">
                                <dt class="text-sm font-medium text-purple-600">Created</dt>
                                <dd class="mt-1 text-sm font-medium text-purple-900">{{ formatDate(pass.created_at) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Worker QR Modal -->
        <div v-if="selectedWorkerQR" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="selectedWorkerQR = null"></div>
                <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full sm:p-6">
                    <div class="text-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            {{ selectedWorkerQR.worker_name }} - QR Code
                        </h3>
                        <img :src="selectedWorkerQR.qr_code_url" alt="Worker QR Code" class="w-full max-w-sm mx-auto rounded-lg border-2 border-gray-200" />
                        <p class="mt-3 text-sm text-gray-500">Position: {{ selectedWorkerQR.worker_position || 'N/A' }}</p>
                        <p class="mt-1 text-xs text-gray-400">Scan this QR code for quick admission</p>
                        <div class="mt-5">
                            <a
                                :href="selectedWorkerQR.qr_code_url"
                                download
                                class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md transition"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download QR Code
                            </a>
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
                                <p class="text-sm text-gray-500">Please provide a reason for rejecting this pass.</p>
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
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                        <button
                            @click="confirmReject"
                            :disabled="rejectForm.processing"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:col-start-2 sm:text-sm disabled:opacity-50"
                        >
                            {{ rejectForm.processing ? 'Rejecting...' : 'Reject Pass' }}
                        </button>
                        <button
                            @click="closeRejectModal"
                            :disabled="rejectForm.processing"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1 sm:text-sm disabled:opacity-50"
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
                                <p class="text-sm text-gray-500">Are you sure you want to revoke this pass? You can optionally provide a reason.</p>
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
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                        <button
                            @click="confirmRevoke"
                            :disabled="revokeForm.processing"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 sm:col-start-2 sm:text-sm disabled:opacity-50"
                        >
                            {{ revokeForm.processing ? 'Revoking...' : 'Revoke Pass' }}
                        </button>
                        <button
                            @click="closeRevokeModal"
                            :disabled="revokeForm.processing"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1 sm:text-sm disabled:opacity-50"
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
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

// Props
const props = defineProps({
    pass: Object,
    qrCodeUrl: String,
});

// Page data
const page = usePage();
const user = computed(() => page.props.auth?.user);

// Modals
const showRejectModal = ref(false);
const showRevokeModal = ref(false);
const selectedWorkerQR = ref(null);

// Worker history expansion
const expandedWorkers = ref(new Set());

// Forms
const rejectForm = useForm({
    reason: '',
});

const revokeForm = useForm({
    reason: '',
});

// Computed
const canEdit = computed(() => {
    return ['draft', 'pending'].includes(props.pass.status);
});

const canApprove = computed(() => {
    const hasRole = user.value?.roles?.some(role => ['admin', 'super-admin'].includes(role.name));
    return hasRole && props.pass.status === 'pending';
});

const canReject = computed(() => {
    const hasRole = user.value?.roles?.some(role => ['admin', 'super-admin'].includes(role.name));
    return hasRole && props.pass.status === 'pending';
});

const canRevoke = computed(() => {
    return ['approved', 'active'].includes(props.pass.status);
});

const admittedWorkersCount = computed(() => {
    if (!props.pass.workers) return 0;
    return props.pass.workers.filter(w => w.is_admitted).length;
});

// Worker scan history helpers
const workerHasScans = (worker) => {
    if (!props.pass.scans) return false;
    return props.pass.scans.some(scan =>
        scan.scan_data?.worker_id === worker.id
    );
};

const getWorkerScans = (worker) => {
    if (!props.pass.scans) return [];
    return props.pass.scans.filter(scan =>
        scan.scan_data?.worker_id === worker.id
    );
};

const getWorkerScanCount = (worker) => {
    return getWorkerScans(worker).length;
};

const toggleWorkerHistory = (workerId) => {
    if (expandedWorkers.value.has(workerId)) {
        expandedWorkers.value.delete(workerId);
    } else {
        expandedWorkers.value.add(workerId);
    }
    // Force reactivity
    expandedWorkers.value = new Set(expandedWorkers.value);
};

const showWorkerQR = (worker) => {
    selectedWorkerQR.value = worker;
};

// Methods
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

const getScanTypeClass = (scanType) => {
    return scanType === 'entry' ? 'bg-green-500' : 'bg-blue-500';
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const approvePass = () => {
    if (confirm('Are you sure you want to approve this pass?')) {
        router.post(route('passes.approve', props.pass.id), {}, {
            preserveScroll: true,
        });
    }
};

const openRejectModal = () => {
    rejectForm.reason = '';
    rejectForm.errors = {};
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    rejectForm.reset();
};

const confirmReject = () => {
    rejectForm.post(route('passes.reject', props.pass.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeRejectModal();
        },
    });
};

const openRevokeModal = () => {
    revokeForm.reason = '';
    showRevokeModal.value = true;
};

const closeRevokeModal = () => {
    showRevokeModal.value = false;
    revokeForm.reset();
};

const confirmRevoke = () => {
    revokeForm.post(route('passes.revoke', props.pass.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeRevokeModal();
        },
    });
};
</script>
