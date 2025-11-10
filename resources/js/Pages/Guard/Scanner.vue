
<template>
    <div class="space-y-6">
        <div
            v-if="!isOnline"
            class="rounded-2xl border border-yellow-500/40 bg-yellow-500/10 p-4 flex flex-wrap gap-3 items-center justify-between text-sm text-yellow-100"
        >
            <div>
                <p class="font-semibold">Offline mode</p>
                <p class="text-yellow-200/80">Scans will be queued and synced when your device reconnects.</p>
            </div>
            <div class="flex items-center gap-3">
                <span v-if="offlineQueue.length" class="text-xs uppercase tracking-wide">
                    Pending scans: {{ offlineQueue.length }}
                </span>
                <button
                    type="button"
                    class="rounded-lg border border-yellow-400/60 px-3 py-1 text-xs font-semibold hover:bg-yellow-400/20"
                    @click="flushOfflineScans"
                >
                    Retry sync
                </button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 space-y-6">
                    <template v-if="gates.length">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">
                                    Active Gate
                                </label>
                                <select
                                    v-model="scanForm.gate_id"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option
                                        v-for="gate in gates"
                                        :key="gate.id"
                                        :value="gate.id"
                                    >
                                        {{ gate.name }}
                                    </option>
                                </select>
                                <p class="mt-2 text-xs text-slate-400">
                                    You are assigned to {{ gates.length }} gate{{ gates.length > 1 ? 's' : '' }}.
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">
                                    Scan Type
                                </label>
                                <div class="flex gap-2">
                                    <button
                                        v-for="type in scanTypes"
                                        :key="type.value"
                                        type="button"
                                        class="flex-1 rounded-xl border px-3 py-2 text-sm font-semibold transition"
                                        :class="scanForm.scan_type === type.value
                                            ? 'border-blue-500 bg-blue-500/20 text-white'
                                            : 'border-slate-700 text-slate-300 hover:border-slate-500'"
                                        @click="scanForm.scan_type = type.value"
                                    >
                                        {{ type.label }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Camera Scanner
                                    </label>
                                    <div class="flex items-center gap-2 text-xs">
                                        <select
                                            v-model="selectedCameraId"
                                            class="rounded-lg border border-slate-700 bg-slate-900/60 px-2 py-1"
                                        >
                                            <option
                                                v-for="device in cameraDevices"
                                                :key="device.deviceId"
                                                :value="device.deviceId"
                                            >
                                                {{ device.label || 'Camera' }}
                                            </option>
                                        </select>
                                        <button
                                            v-if="scanning"
                                            type="button"
                                            class="rounded-lg border border-slate-600 px-2 py-1 hover:bg-slate-800 text-xs"
                                            @click="toggleTorch"
                                        >
                                            Torch {{ torchEnabled ? 'On' : 'Off' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg border border-blue-500 px-2 py-1 text-xs font-semibold text-blue-200 hover:bg-blue-500/10"
                                            @click="scanning ? stopScanner() : startScanner()"
                                        >
                                            {{ scanning ? 'Stop' : 'Start' }}
                                        </button>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-800 bg-slate-950/40 overflow-hidden h-64 flex items-center justify-center">
                                    <video
                                        ref="videoRef"
                                        class="w-full h-full object-cover"
                                        playsinline
                                        muted
                                    ></video>
                                    <p v-if="!scanning" class="text-xs text-slate-500 absolute">
                                        Start scanner to use the camera
                                    </p>
                                </div>
                                <p v-if="scanningError" class="text-xs text-red-400">
                                    {{ scanningError }}
                                </p>
                            </div>
                            <div class="space-y-4">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Manual Entry
                                </label>
                                <div class="flex rounded-xl border border-slate-700 bg-slate-900/70 p-1">
                                    <button
                                        v-for="method in methodOptions"
                                        :key="method.value"
                                        type="button"
                                        class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold transition"
                                        :class="scanForm.method === method.value
                                            ? 'bg-blue-500/20 text-white'
                                            : 'text-slate-300 hover:bg-slate-800'"
                                        @click="scanForm.method = method.value"
                                    >
                                        {{ method.label }}
                                    </button>
                                </div>
                                <input
                                    v-model="scanForm.code"
                                    type="text"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-4 py-3 text-lg tracking-widest text-white focus:border-blue-500 focus:ring-blue-500"
                                    :placeholder="scanForm.method === 'pin' ? 'Enter 6-digit PIN' : 'Scan QR or type pass number'"
                                    autocomplete="off"
                                />
                                <div class="flex flex-wrap gap-3 items-center justify-between">
                                    <p class="text-xs text-slate-400">Tip: When offline, scans queue automatically.</p>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold shadow-lg shadow-blue-600/30 transition hover:bg-blue-500 disabled:opacity-50"
                                        :disabled="scanForm.processing || !scanForm.code"
                                        @click="submitScan"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618V15.5a4.5 4.5 0 01-4.5 4.5h-11A3.5 3.5 0 012 16.5v-9A3.5 3.5 0 015.5 4h8a1 1 0 01.894.553L16 7h2" />
                                        </svg>
                                        <span>Validate</span>
                                    </button>
                                </div>
                                <p v-if="localAlert" class="text-xs" :class="localAlert.class">
                                    {{ localAlert.message }}
                                </p>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="text-center py-8">
                            <p class="text-lg font-semibold text-white">No gates assigned</p>
                            <p class="text-sm text-slate-400">
                                Please contact an administrator to assign you to a gate before scanning passes.
                            </p>
                        </div>
                    </template>
                </div>

                <div v-if="scanResult" class="rounded-2xl border p-5" :class="resultClasses[scanResult.status]">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm uppercase tracking-wide font-semibold">{{ scanResult.status }}</p>
                            <p class="text-lg font-bold">{{ scanResult.message }}</p>
                        </div>
                        <div class="text-right text-sm text-slate-300">
                            <p>Gate: {{ scanResult.gate?.name }}</p>
                            <p v-if="scanResult.pass">Pass #: {{ scanResult.pass.pass_number }}</p>
                        </div>
                    </div>
                    <div v-if="scanResult.pass" class="grid gap-2 md:grid-cols-3 text-sm">
                        <div>
                            <p class="text-xs uppercase text-slate-400">Visitor</p>
                            <p class="font-semibold">{{ scanResult.pass.visitor_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-slate-400">Status</p>
                            <p class="font-semibold capitalize">{{ scanResult.pass.status }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-slate-400">Valid Until</p>
                            <p class="font-semibold">{{ formatDate(scanResult.pass.valid_to) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white">Local Scan History</h2>
                        <button
                            v-if="localScanHistory.length"
                            type="button"
                            class="text-xs text-slate-400 hover:text-slate-200"
                            @click="clearLocalHistory"
                        >
                            Clear
                        </button>
                    </div>
                    <div v-if="localScanHistory.length" class="space-y-3 text-sm">
                        <div
                            v-for="entry in localScanHistory"
                            :key="entry.timestamp"
                            class="flex items-center justify-between rounded-xl border border-slate-800 px-4 py-3 bg-slate-900/50"
                        >
                            <div>
                                <p class="font-semibold text-white">{{ entry.visitor_name || entry.code }}</p>
                                <p class="text-xs text-slate-400">Pass #{{ entry.pass_number || 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full" :class="badgeClass(entry.result)">
                                    {{ entry.result }}
                                </span>
                                <p class="text-xs text-slate-400 mt-1">{{ formatDate(entry.scanned_at) }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-xs text-slate-400">Local history will appear here after your first scan.</p>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Shift Status</p>
                            <p class="text-lg font-semibold text-white">
                                {{ currentShift ? 'Active' : 'Inactive' }}
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full"
                            :class="currentShift ? 'bg-green-500/20 text-green-300' : 'bg-slate-700 text-slate-300'"
                        >
                            {{ currentShift ? 'On Duty' : 'Off Duty' }}
                        </span>
                    </div>
                    <div v-if="currentShift" class="text-xs text-slate-300 space-y-2">
                        <p>Started: {{ formatDate(currentShift.started_at) }}</p>
                        <p v-if="currentShift.gate">Gate: {{ currentShift.gate.name }}</p>
                        <textarea
                            v-model="endShiftForm.notes"
                            rows="2"
                            class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-sm"
                            placeholder="Shift notes (optional)"
                        ></textarea>
                        <button
                            type="button"
                            class="w-full rounded-xl bg-red-500/80 px-4 py-2 text-sm font-semibold hover:bg-red-500"
                            @click="endShift"
                            :disabled="endShiftForm.processing"
                        >
                            End Shift
                        </button>
                    </div>
                    <div v-else>
                        <textarea
                            v-model="startShiftForm.notes"
                            rows="2"
                            class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-sm mb-3"
                            placeholder="Shift notes (optional)"
                        ></textarea>
                        <button
                            type="button"
                            class="w-full rounded-xl bg-green-500/80 px-4 py-2 text-sm font-semibold hover:bg-green-500 disabled:opacity-50"
                            :disabled="startShiftForm.processing"
                            @click="startShift"
                        >
                            Start Shift
                        </button>
                    </div>
                    <p v-if="shiftStatus" class="text-xs" :class="statusClass(shiftStatus.status)">
                        {{ shiftStatus.message }}
                    </p>
                </div>

                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Issue Reporting</p>
                            <p class="text-lg font-semibold text-white">Alert Security</p>
                        </div>
                        <span class="text-xs text-slate-400">#{{ issueTypes.length }} types</span>
                    </div>
                    <select
                        v-model="issueForm.issue_type"
                        class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-sm"
                    >
                        <option v-for="type in issueTypes" :key="type" :value="type">
                            {{ formatIssueType(type) }}
                        </option>
                    </select>
                    <select
                        v-model="issueForm.severity"
                        class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-sm"
                    >
                        <option v-for="severity in issueSeverities" :key="severity" :value="severity">
                            {{ severity }}
                        </option>
                    </select>
                    <input
                        v-model="issueForm.pass_code"
                        type="text"
                        class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-sm"
                        placeholder="Pass # / PIN (optional)"
                    />
                    <textarea
                        v-model="issueForm.description"
                        rows="3"
                        class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-sm"
                        placeholder="Describe the issue..."
                    ></textarea>
                    <button
                        type="button"
                        class="w-full rounded-xl bg-orange-500/80 px-4 py-2 text-sm font-semibold hover:bg-orange-500 disabled:opacity-50"
                        :disabled="issueForm.processing"
                        @click="submitIssue"
                    >
                        Submit Report
                    </button>
                    <button
                        type="button"
                        class="w-full rounded-xl border border-red-500/60 px-4 py-2 text-sm font-semibold text-red-200 hover:bg-red-500/10 disabled:opacity-50"
                        :disabled="issueForm.processing"
                        @click="sendEmergencyAlert"
                    >
                        Emergency Alert
                    </button>
                    <p v-if="issueStatus" class="text-xs" :class="statusClass(issueStatus.status)">
                        {{ issueStatus.message }}
                    </p>
                </div>

                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-400 mb-4">Today’s Activity</p>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                            <p class="text-2xl font-bold text-white">{{ stats.total_today }}</p>
                            <p class="text-xs text-slate-400 mt-1">Total Scans</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                            <p class="text-2xl font-bold text-green-300">{{ stats.success }}</p>
                            <p class="text-xs text-slate-400 mt-1">Success</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                            <p class="text-2xl font-bold text-yellow-300">{{ stats.warnings }}</p>
                            <p class="text-xs text-slate-400 mt-1">Warnings</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                            <p class="text-2xl font-bold text-red-300">{{ stats.failed }}</p>
                            <p class="text-xs text-slate-400 mt-1">Failed</p>
                        </div>
                    </div>
                </div>

                <div v-if="showApprovalControls" class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-white">Pass Actions</p>
                        <p class="text-xs text-slate-400">Requires approval permissions</p>
                    </div>
                    <div class="flex flex-wrap gap-3 items-center">
                        <button
                            v-if="canApprove"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-green-500/80 px-4 py-2 text-sm font-semibold hover:bg-green-500 disabled:opacity-50"
                            :disabled="approveForm.processing"
                            @click="approvePass"
                        >
                            Approve Pass
                        </button>
                        <div v-if="canReject" class="flex flex-wrap gap-2 items-center">
                            <input
                                v-model="rejectForm.reason"
                                type="text"
                                class="rounded-xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-sm min-w-[200px]"
                                placeholder="Rejection reason"
                            />
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-xl bg-red-500/80 px-4 py-2 text-sm font-semibold hover:bg-red-500 disabled:opacity-50"
                                :disabled="rejectForm.processing || !rejectForm.reason"
                                @click="rejectPass"
                            >
                                Reject Pass
                            </button>
                        </div>
                    </div>
                    <p v-if="passActionStatus" class="text-xs" :class="statusClass(passActionStatus.status)">
                        {{ passActionStatus.message }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-white">Recent Scans</h2>
                <p class="text-xs text-slate-400">Last 10 scans</p>
            </div>
            <div v-if="recentScans.length" class="space-y-4">
                <div
                    v-for="scan in recentScans"
                    :key="scan.id"
                    class="flex items-center justify-between rounded-xl border border-slate-800 px-4 py-3 bg-slate-900/60"
                >
                    <div>
                        <p class="text-sm font-semibold text-white">
                            {{ scan.pass?.visitor_name || 'Unknown visitor' }}
                        </p>
                        <p class="text-xs text-slate-400">Pass #{{ scan.pass?.pass_number || 'N/A' }}</p>
                        <p class="text-xs text-slate-500 mt-1">Guard: {{ scan.scanned_by?.name || 'You' }}</p>
                    </div>
                    <div class="text-right">
                        <span
                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full"
                            :class="badgeClass(scan.result)"
                        >
                            {{ scan.result }}
                        </span>
                        <p class="text-xs text-slate-400 mt-1">{{ formatDate(scan.scanned_at) }}</p>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-slate-400 text-center">No scans recorded yet.</p>
        </div>
    </div>
</template>
<script setup>
import { BrowserMultiFormatReader } from '@zxing/browser';
import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import GuardLayout from '@/Layouts/GuardLayout.vue';
import { cachePass, getCachedPass, trimCachedPasses, isIndexedDbSupported } from '@/utils/indexedDb';

defineOptions({ layout: GuardLayout });

const props = defineProps({
    gates: { type: Array, default: () => [] },
    defaultGateId: { type: [Number, String, null], default: null },
    recentScans: { type: Array, default: () => [] },
    currentShift: { type: Object, default: null },
    issueTypes: { type: Array, default: () => [] },
    issueSeverities: { type: Array, default: () => ['low', 'medium', 'high'] },
    stats: { type: Object, default: () => ({}) },
    canApprove: { type: Boolean, default: false },
    canReject: { type: Boolean, default: false },
});

const page = usePage();
const scanResult = computed(() => page.props.flash?.scanResult ?? null);
const shiftStatus = computed(() => page.props.flash?.shiftStatus ?? null);
const issueStatus = computed(() => page.props.flash?.issueStatus ?? null);
const passActionStatus = computed(() => page.props.flash?.passActionStatus ?? null);
const stats = computed(() => ({
    total_today: props.stats?.total_today ?? 0,
    success: props.stats?.success ?? 0,
    warnings: props.stats?.warnings ?? 0,
    failed: props.stats?.failed ?? 0,
}));
const supportsIndexedDb = isIndexedDbSupported();
const canApprove = computed(() => props.canApprove);
const canReject = computed(() => props.canReject);
const showApprovalControls = computed(() => (canApprove.value || canReject.value) && scanResult.value?.pass);

const storedDeviceId =
    (typeof window !== 'undefined' && window.localStorage.getItem('guardDeviceId')) ||
    (typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() : Date.now().toString());

if (typeof window !== 'undefined') {
    window.localStorage.setItem('guardDeviceId', storedDeviceId);
}

const scanForm = useForm({
    gate_id: props.defaultGateId || '',
    method: 'qr',
    code: '',
    scan_type: 'entry',
    device_id: storedDeviceId,
    was_offline: false,
});

const startShiftForm = useForm({
    gate_id: props.defaultGateId || '',
    notes: '',
});

const endShiftForm = useForm({
    notes: '',
});

const issueForm = useForm({
    gate_id: props.defaultGateId || '',
    pass_code: '',
    issue_type: props.issueTypes[0] || 'other',
    severity: 'low',
    description: '',
});

const approveForm = useForm({});
const rejectForm = useForm({
    reason: '',
});
watch(
    () => props.defaultGateId,
    (value) => {
        if (value && !scanForm.gate_id) {
            scanForm.gate_id = value;
        }
        if (value && !startShiftForm.gate_id) {
            startShiftForm.gate_id = value;
        }
        if (value && !issueForm.gate_id) {
            issueForm.gate_id = value;
        }
    }
);

const scanTypes = [
    { label: 'Entry', value: 'entry' },
    { label: 'Exit', value: 'exit' },
    { label: 'Validate', value: 'validation' },
];

const methodOptions = [
    { label: 'QR / Pass #', value: 'qr' },
    { label: 'PIN', value: 'pin' },
];

const resultClasses = {
    success: 'border-green-600/40 bg-green-500/10',
    warning: 'border-yellow-600/40 bg-yellow-500/10',
    error: 'border-red-600/40 bg-red-500/10',
};

const badgeClass = (result) => {
    switch (result) {
        case 'success':
            return 'bg-green-500/20 text-green-300';
        case 'warning':
            return 'bg-yellow-500/20 text-yellow-300';
        default:
            return 'bg-red-500/20 text-red-300';
    }
};

const statusClass = (status) => {
    switch (status) {
        case 'success':
            return 'text-green-300';
        case 'warning':
            return 'text-yellow-300';
        default:
            return 'text-red-300';
    }
};

const formatDate = (value) => {
    if (!value) {
        return '';
    }
    return new Date(value).toLocaleString();
};

const formatIssueType = (type) => {
    return type
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
};

const localAlert = ref(null);
const isOnline = ref(typeof navigator !== 'undefined' ? navigator.onLine : true);
const offlineQueue = ref(JSON.parse(typeof window !== 'undefined' ? window.localStorage.getItem('guardOfflineScans') ?? '[]' : '[]'));
const localScanHistory = ref(JSON.parse(typeof window !== 'undefined' ? window.localStorage.getItem('guardScanHistory') ?? '[]' : '[]'));

const saveOfflineQueue = () => {
    if (typeof window !== 'undefined') {
        window.localStorage.setItem('guardOfflineScans', JSON.stringify(offlineQueue.value));
    }
};

const saveLocalHistory = () => {
    if (typeof window !== 'undefined') {
        window.localStorage.setItem('guardScanHistory', JSON.stringify(localScanHistory.value));
    }
};

const addToLocalHistory = (entry) => {
    localScanHistory.value.unshift(entry);
    localScanHistory.value = localScanHistory.value.slice(0, 10);
    saveLocalHistory();
};

const clearLocalHistory = () => {
    localScanHistory.value = [];
    saveLocalHistory();
};

const queueOfflineScan = async () => {
    const payload = {
        ...scanForm.data(),
        was_offline: true,
        timestamp: new Date().toISOString(),
    };
    offlineQueue.value.push(payload);
    saveOfflineQueue();

    let cached = null;
    if (supportsIndexedDb) {
        cached = await getCachedPass(payload.code);
    }

    addToLocalHistory({
        visitor_name: cached?.visitor_name,
        pass_number: cached?.pass_number || payload.code,
        result: 'queued',
        scanned_at: payload.timestamp,
    });
};
const flushOfflineScans = async () => {
    if (!offlineQueue.value.length || !isOnline.value) {
        return;
    }

    const queueCopy = [...offlineQueue.value];
    for (const payload of queueCopy) {
        try {
            await axios.post(route('guard.scans.store'), payload);
            offlineQueue.value.shift();
            saveOfflineQueue();
        } catch (error) {
            offlineQueue.value.shift();
            saveOfflineQueue();
            const message = error.response?.data?.message || 'Unable to sync queued scans right now.';
            localAlert.value = {
                class: 'text-red-300',
                message,
            };
            addToLocalHistory({
                pass_number: payload.code,
                result: 'failed',
                scanned_at: new Date().toISOString(),
            });
        }
    }
};

const handleOffline = () => {
    isOnline.value = false;
};

const handleOnline = () => {
    isOnline.value = true;
    flushOfflineScans();
};

const submitScan = async () => {
    if (!scanForm.code) {
        return;
    }

    if (!isOnline.value) {
        await queueOfflineScan();
        localAlert.value = {
            class: 'text-yellow-300',
            message: 'Scan stored locally. It will sync when you are back online.',
        };
        scanForm.code = '';
        return;
    }

    localAlert.value = null;
    scanForm.was_offline = false;
    await scanForm.post(route('guard.scans.store'), {
        preserveScroll: true,
        onSuccess: () => {
            if (scanResult.value?.pass) {
                addToLocalHistory({
                    visitor_name: scanResult.value.pass.visitor_name,
                    pass_number: scanResult.value.pass.pass_number,
                    result: scanResult.value.status === 'error' ? 'failed' : scanResult.value.status,
                    scanned_at: new Date().toISOString(),
                });
            }
            scanForm.code = '';
        },
    });
};

const startShift = () => {
    startShiftForm.post(route('guard.shifts.start'), {
        preserveScroll: true,
        onSuccess: () => {
            startShiftForm.reset('notes');
        },
    });
};

const endShift = () => {
    endShiftForm.post(route('guard.shifts.end'), {
        preserveScroll: true,
        onSuccess: () => {
            endShiftForm.reset('notes');
        },
    });
};

const submitIssue = () => {
    issueForm.post(route('guard.issues.store'), {
        preserveScroll: true,
        onSuccess: () => {
            issueForm.reset('pass_code', 'description');
        },
    });
};

const sendEmergencyAlert = () => {
    issueForm.issue_type = 'other';
    issueForm.severity = 'high';
    if (!issueForm.description) {
        issueForm.description = 'Emergency alert triggered at gate.';
    }
    submitIssue();
};

const approvePass = () => {
    if (!scanResult.value?.pass || !canApprove.value) {
        return;
    }
    approveForm.post(route('guard.passes.approve', scanResult.value.pass.id), {
        preserveScroll: true,
    });
};

const rejectPass = () => {
    if (!scanResult.value?.pass || !canReject.value || !rejectForm.reason) {
        return;
    }
    rejectForm.post(route('guard.passes.reject', scanResult.value.pass.id), {
        preserveScroll: true,
        onSuccess: () => rejectForm.reset('reason'),
    });
};
const videoRef = ref(null);
const codeReader = new BrowserMultiFormatReader();
const cameraDevices = ref([]);
const selectedCameraId = ref(null);
const scanning = ref(false);
const scanningError = ref('');
const torchEnabled = ref(false);
let videoControls = null;
let scanLock = false;
let scanTimeout = null;

const enumerateCameras = async () => {
    try {
        const devices = await BrowserMultiFormatReader.listVideoInputDevices();
        cameraDevices.value = devices;
        if (!selectedCameraId.value && devices.length) {
            selectedCameraId.value = devices[0].deviceId;
        }
    } catch (error) {
        scanningError.value = 'Unable to list camera devices.';
    }
};

const resetScanTimeout = () => {
    clearTimeout(scanTimeout);
    if (!scanning.value) {
        return;
    }
    scanTimeout = setTimeout(() => {
        scanningError.value = 'No QR detected. Adjust camera or lighting.';
    }, 30000);
};

const startScanner = async () => {
    if (scanning.value) {
        return;
    }

    await enumerateCameras();
    if (!selectedCameraId.value) {
        scanningError.value = 'No camera available.';
        return;
    }

    try {
        scanning.value = true;
        scanningError.value = '';
        videoControls = await codeReader.decodeFromVideoDevice(
            selectedCameraId.value,
            videoRef.value,
            (result) => {
                if (result) {
                    handleScanResult(result.getText());
                }
            }
        );
        resetScanTimeout();
    } catch (error) {
        scanningError.value = 'Unable to start camera. Please check permissions.';
        scanning.value = false;
    }
};

const stopScanner = () => {
    if (videoControls) {
        videoControls.stop();
        videoControls = null;
    }
    codeReader.reset();
    scanning.value = false;
    torchEnabled.value = false;
    clearTimeout(scanTimeout);
};

const handleScanResult = (text) => {
    if (scanLock) {
        return;
    }
    scanLock = true;
    scanForm.method = 'qr';
    scanForm.code = text;
    submitScan();
    addToLocalHistory({
        code: text,
        pass_number: text,
        result: 'scanned',
        scanned_at: new Date().toISOString(),
    });
    setTimeout(() => {
        scanLock = false;
        resetScanTimeout();
    }, 1500);
};

const toggleTorch = async () => {
    if (!videoControls || !videoControls.switchTorch) {
        scanningError.value = 'Torch not supported on this device.';
        torchEnabled.value = false;
        return;
    }

    try {
        torchEnabled.value = !torchEnabled.value;
        await videoControls.switchTorch(torchEnabled.value);
    } catch (error) {
        torchEnabled.value = false;
        scanningError.value = 'Unable to toggle torch.';
    }
};

watch(selectedCameraId, () => {
    if (scanning.value) {
        stopScanner();
        startScanner();
    }
});

watch(
    scanResult,
    async (value) => {
        if (value?.pass) {
            addToLocalHistory({
                visitor_name: value.pass.visitor_name,
                pass_number: value.pass.pass_number,
                result: value.status === 'error' ? 'failed' : value.status,
                scanned_at: new Date().toISOString(),
            });

            if (supportsIndexedDb) {
                await cachePass(value.pass, [value.input_code, value.pass.pass_number, value.pass.uuid]);
                await trimCachedPasses(100);
            }
        }

        rejectForm.reset('reason');
    }
);

onMounted(() => {
    enumerateCameras();
    window.addEventListener('offline', handleOffline);
    window.addEventListener('online', handleOnline);
    if (supportsIndexedDb) {
        trimCachedPasses(100);
    }
});

onBeforeUnmount(() => {
    stopScanner();
    window.removeEventListener('offline', handleOffline);
    window.removeEventListener('online', handleOnline);
});
</script>
