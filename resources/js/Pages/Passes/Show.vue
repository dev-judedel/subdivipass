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

                    <!-- Visitor Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
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

                    <!-- Vehicle Information -->
                    <div v-if="pass.vehicle_plate || pass.vehicle_model" class="bg-white rounded-lg shadow-sm p-6">
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
                                                    By {{ scan.guard?.name }} • {{ formatDate(scan.scanned_at) }}
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
