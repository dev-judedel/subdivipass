<template>
    <div class="space-y-6">
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6">
            <template v-if="gates.length">
                <div class="grid gap-4 md:grid-cols-2 mb-6">
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

                <form @submit.prevent="submitScan" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="md:col-span-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">
                                Input Method
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
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">
                                {{ scanForm.method === 'pin' ? 'PIN Code' : 'QR / Pass Number' }}
                            </label>
                            <input
                                v-model="scanForm.code"
                                type="text"
                                class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-4 py-3 text-lg tracking-widest text-white focus:border-blue-500 focus:ring-blue-500"
                                :placeholder="scanForm.method === 'pin' ? 'Enter 6-digit PIN' : 'Scan QR or type pass number'"
                                autocomplete="off"
                            />
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3 items-center justify-between">
                        <p class="text-xs text-slate-400">Tip: Use the QR scanner on your device or enter the PIN manually.</p>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold shadow-lg shadow-blue-600/30 transition hover:bg-blue-500 disabled:opacity-50"
                            :disabled="scanForm.processing || !scanForm.code"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618V15.5a4.5 4.5 0 01-4.5 4.5h-11A3.5 3.5 0 012 16.5v-9A3.5 3.5 0 015.5 4h8a1 1 0 01.894.553L16 7h2" />
                            </svg>
                            <span>Validate</span>
                        </button>
                    </div>
                </form>
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
import { computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import GuardLayout from '@/Layouts/GuardLayout.vue';

defineOptions({ layout: GuardLayout });

const props = defineProps({
    gates: { type: Array, default: () => [] },
    defaultGateId: { type: [Number, String, null], default: null },
    recentScans: { type: Array, default: () => [] },
});

const page = usePage();
const scanResult = computed(() => page.props.flash?.scanResult ?? null);

const scanForm = useForm({
    gate_id: props.defaultGateId || '',
    method: 'qr',
    code: '',
    scan_type: 'entry',
    device_id: '',
});

watch(
    () => props.defaultGateId,
    (value) => {
        if (value && !scanForm.gate_id) {
            scanForm.gate_id = value;
        }
    }
);

const submitScan = () => {
    scanForm.post(route('guard.scans.store'), {
        preserveScroll: true,
        onSuccess: () => {
            scanForm.code = '';
        },
    });
};

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

const formatDate = (value) => {
    if (!value) {
        return '';
    }
    return new Date(value).toLocaleString();
};
</script>
