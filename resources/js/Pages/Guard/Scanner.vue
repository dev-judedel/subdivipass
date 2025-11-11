
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

        <div
            v-if="canInstallPwa"
            class="rounded-2xl border border-blue-500/40 bg-blue-500/10 p-4 flex flex-wrap gap-3 items-center justify-between text-sm text-blue-100"
        >
            <div>
                <p class="font-semibold">Install guard console</p>
                <p class="text-blue-200/80">Add SubdiPass Guard to your home screen for full-screen scanning.</p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="rounded-lg border border-blue-400/60 px-3 py-1 text-xs font-semibold hover:bg-blue-400/20"
                    @click="dismissPwaInstall"
                >
                    Later
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-blue-500 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-blue-500/30 hover:bg-blue-400"
                    @click="promptPwaInstall"
                >
                    Install
                </button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Network Status</p>
                    <p class="text-lg font-semibold text-white">
                        {{ isOnline ? 'Connected' : 'Offline (queued only)' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ isOnline ? 'Scans sync instantly' : 'Scans queue locally until connection resumes' }}
                    </p>
                </div>
                <span
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                    :class="isOnline ? 'bg-green-500/20 text-green-200' : 'bg-yellow-500/20 text-yellow-200'"
                >
                    <span class="h-2.5 w-2.5 rounded-full" :class="isOnline ? 'bg-green-400' : 'bg-yellow-400'"></span>
                    {{ isOnline ? 'Online' : 'Offline' }}
                </span>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Push Notifications</p>
                        <p class="text-lg font-semibold text-white">{{ pushStatusLabel }}</p>
                    </div>
                    <span v-if="pushPermission === 'denied'" class="text-xs text-red-400 font-semibold">
                        Allow in browser
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Get alerts for escalations, security broadcasts, and sync issues even when the app is closed.
                </p>
                <div class="mt-3 flex flex-wrap gap-3">
                    <button
                        v-if="pushSupported && pushCanSubscribe && !pushEnabled"
                        type="button"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold shadow-lg shadow-blue-600/30 hover:bg-blue-500 disabled:opacity-50"
                        :disabled="pushBusy"
                        @click="enablePushNotifications"
                    >
                        Enable alerts
                    </button>
                    <button
                        v-if="pushSupported && pushEnabled"
                        type="button"
                        class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800 disabled:opacity-50"
                        :disabled="pushBusy"
                        @click="disablePushNotifications"
                    >
                        Disable alerts
                    </button>
                    <p v-if="!pushSupported" class="text-xs text-slate-500">
                        Push notifications are not available on this device.
                    </p>
                </div>
                <p v-if="pushErrorMessage" class="mt-2 text-xs text-red-400">
                    {{ pushErrorMessage }}
                </p>
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
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-3">
                                        Camera Scanner
                                    </label>
                                    <div class="flex flex-wrap items-center gap-2 text-xs mb-3">
                                        <select
                                            v-model="selectedCameraId"
                                            class="rounded-lg border border-slate-700 bg-slate-900/60 px-2 py-1 text-xs"
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
                                            class="rounded-lg border border-slate-600 px-3 py-1 hover:bg-slate-800 text-xs whitespace-nowrap"
                                            @click="toggleTorch"
                                        >
                                            💡 {{ torchEnabled ? 'On' : 'Off' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg border px-3 py-1 text-xs font-semibold whitespace-nowrap"
                                            :class="scanning ? 'border-red-500 text-red-200 hover:bg-red-500/10' : 'border-blue-500 text-blue-200 hover:bg-blue-500/10'"
                                            @click="scanning ? stopScanner() : startScanner()"
                                        >
                                            {{ scanning ? '⏹ Stop' : '▶ Start' }}
                                        </button>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-800 bg-slate-950/40 overflow-hidden h-64 flex items-center justify-center relative">
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
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-white truncate">
                                    {{ entry.visitor_name || (entry.code?.length > 30 ? entry.code.substring(0, 30) + '...' : entry.code) }}
                                </p>
                                <p class="text-xs text-slate-400 truncate">
                                    Pass #{{ entry.pass_number && entry.pass_number.length < 50 ? entry.pass_number : 'Processing...' }}
                                </p>
                            </div>
                            <div class="text-right ml-3 flex-shrink-0">
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
                <div>
                    <h2 class="text-lg font-semibold text-white">Recent Scans</h2>
                    <p class="text-xs text-slate-400 mt-1">Database records - Refresh page to update</p>
                </div>
                <p class="text-xs text-slate-400">Last 10</p>
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
import { usePwaInstallPrompt } from '@/composables/usePwaInstallPrompt';
import { usePushNotifications } from '@/composables/usePushNotifications';

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
const scanResult = computed(() => {
    const result = page.props.flash?.scanResult ?? null;
    if (result) {
        console.log('🔍 scanResult computed:', result);
    }
    return result;
});
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

const { canInstall: canInstallPwa, promptInstall: promptPwaInstall, dismissInstall: dismissPwaInstall } =
    usePwaInstallPrompt();
const {
    isSupported: pushSupported,
    statusLabel: pushStatusLabel,
    permission: pushPermission,
    isBusy: pushBusy,
    errorMessage: pushErrorMessage,
    hasSubscription: pushEnabled,
    canSubscribe: pushCanSubscribe,
    subscribe: subscribeToPush,
    unsubscribe: unsubscribeFromPush,
} = usePushNotifications();

const enablePushNotifications = () => {
    subscribeToPush();
};

const disablePushNotifications = () => {
    unsubscribeFromPush();
};

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

// Audio feedback
const playSound = (type) => {
    if (typeof window === 'undefined' || typeof AudioContext === 'undefined') return;

    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        // Different sounds for different results
        if (type === 'success') {
            // Success: Two ascending beeps
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        } else if (type === 'warning') {
            // Warning: Single medium beep
            oscillator.frequency.setValueAtTime(600, audioContext.currentTime);
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } else {
            // Error: Two descending beeps
            oscillator.frequency.setValueAtTime(500, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(300, audioContext.currentTime + 0.15);
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        }
    } catch (error) {
        // Audio not supported or failed
    }
};

// Visual flash feedback
const showFlashFeedback = (type) => {
    if (typeof window === 'undefined') return;

    const color = type === 'success' ? 'rgba(34, 197, 94, 0.3)' :
                  type === 'warning' ? 'rgba(234, 179, 8, 0.3)' :
                  'rgba(239, 68, 68, 0.3)';

    const flash = document.createElement('div');
    flash.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: ${color};
        pointer-events: none;
        z-index: 9999;
        animation: flash 0.3s ease-out;
    `;

    const style = document.createElement('style');
    style.textContent = '@keyframes flash { from { opacity: 1; } to { opacity: 0; } }';
    document.head.appendChild(style);
    document.body.appendChild(flash);

    setTimeout(() => {
        document.body.removeChild(flash);
        document.head.removeChild(style);
    }, 300);
};

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
        console.log('❌ No code to submit');
        return;
    }

    console.log('🚀 submitScan called', {
        code_length: scanForm.code.length,
        gate_id: scanForm.gate_id,
        method: scanForm.method,
        isOnline: isOnline.value
    });

    if (!isOnline.value) {
        console.log('⚠️ Offline - queuing scan');
        await queueOfflineScan();
        localAlert.value = {
            class: 'text-yellow-300',
            message: 'Scan stored locally. It will sync when you are back online.',
        };
        playSound('warning');
        scanForm.code = '';
        return;
    }

    localAlert.value = null;
    scanForm.was_offline = false;

    console.log('📡 Sending POST to backend:', route('guard.scans.store'));

    scanForm.post(route('guard.scans.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('✅ Backend responded successfully', page);
            console.log('📦 Flash data:', page.props.flash);

            // Manually trigger the scanResult watch since flash data comes in the response
            const result = page.props.flash?.scanResult;
            if (result) {
                console.log('🔍 Manual trigger - scanResult found:', result);
                // The computed property and watch should handle this, but let's verify
            } else {
                console.warn('⚠️ No scanResult in flash data!');
            }

            // Clear the input field immediately after successful submission
            scanForm.code = '';
        },
        onError: (errors) => {
            console.error('❌ Backend error:', errors);
            // Play error sound on submission failure
            playSound('error');
            showFlashFeedback('error');
        }
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

    console.log('📷 Camera detected QR code:', text.substring(0, 100) + '...');

    scanForm.method = 'qr';
    scanForm.code = text;

    console.log('📤 Submitting to backend...', {
        method: scanForm.method,
        code_length: text.length,
        gate_id: scanForm.gate_id
    });

    submitScan();

    // Don't add to local history here - wait for backend validation result
    // The watch on scanResult will handle adding to history with correct status

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
        if (value) {
            console.log('✅ Scan result received:', value);

            // Play audio and show visual feedback based on scan result
            const resultType = value.status === 'error' ? 'error' : value.status;
            playSound(resultType);
            showFlashFeedback(resultType);

            // Always add to local history (even if error/no pass found)
            const historyEntry = {
                result: value.status === 'error' ? 'failed' : value.status,
                scanned_at: new Date().toISOString(),
                code: value.input_code || 'Unknown',
            };

            if (value.pass) {
                // Valid pass - add full details
                historyEntry.visitor_name = value.pass.visitor_name;
                historyEntry.pass_number = value.pass.pass_number;

                console.log('✅ Adding to local history:', historyEntry);

                // Cache for offline use
                if (supportsIndexedDb) {
                    await cachePass(value.pass, [value.input_code, value.pass.pass_number, value.pass.uuid]);
                    await trimCachedPasses(100);
                }
            } else {
                // Invalid/error - show the input code that was scanned
                historyEntry.pass_number = value.input_code || 'Invalid';
                historyEntry.visitor_name = 'Not Found';
                console.log('⚠️ No pass found, adding error to local history:', historyEntry);
            }

            addToLocalHistory(historyEntry);
            console.log('📝 Local history updated. Total entries:', localScanHistory.value.length);
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
