<template>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white shadow">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Gate Configuration</p>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">Register Gate</h1>
                    <p class="text-sm text-gray-500 mt-1">Assign this gate to a subdivision and define its behavior.</p>
                </div>
                <Link
                    :href="route('gates.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400"
                >
                    Back to list
                </Link>
            </div>
        </div>

        <form class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" @submit.prevent="openConfirm">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Subdivision</label>
                        <select
                            v-model="form.subdivision_id"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                            :class="{ 'border-red-500': form.errors.subdivision_id }"
                        >
                            <option value="">Select subdivision</option>
                            <option v-for="option in subdivisionOptions" :key="option.id" :value="option.id">
                                {{ option.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.subdivision_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.subdivision_id }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Gate Code</label>
                        <input
                            v-model="form.code"
                            type="text"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm uppercase tracking-wide"
                            :class="{ 'border-red-500': form.errors.code }"
                            placeholder="GATE-01"
                        />
                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                            {{ form.errors.code }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Gate Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                        :class="{ 'border-red-500': form.errors.name }"
                        placeholder="Main Gate"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Location Description</label>
                    <textarea
                        v-model="form.location"
                        rows="3"
                        class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                        placeholder="Front entrance near clubhouse"
                    ></textarea>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Gate Type</label>
                        <select
                            v-model="form.type"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm capitalize"
                            :class="{ 'border-red-500': form.errors.type }"
                        >
                            <option v-for="type in typeOptions" :key="type" :value="type">
                                {{ type }}
                            </option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">
                            {{ form.errors.type }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Status</label>
                        <select
                            v-model="form.status"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm capitalize"
                            :class="{ 'border-red-500': form.errors.status }"
                        >
                            <option v-for="status in statusOptions" :key="status" :value="status">
                                {{ status }}
                            </option>
                        </select>
                        <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">
                            {{ form.errors.status }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Latitude</label>
                        <input
                            v-model="form.coordinates.lat"
                            type="number"
                            step="0.000001"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                            :class="{ 'border-red-500': form.errors['coordinates.lat'] }"
                        />
                        <p v-if="form.errors['coordinates.lat']" class="mt-1 text-sm text-red-600">
                            {{ form.errors['coordinates.lat'] }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Longitude</label>
                        <input
                            v-model="form.coordinates.lng"
                            type="number"
                            step="0.000001"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                            :class="{ 'border-red-500': form.errors['coordinates.lng'] }"
                        />
                        <p v-if="form.errors['coordinates.lng']" class="mt-1 text-sm text-red-600">
                            {{ form.errors['coordinates.lng'] }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Notes</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                        placeholder="Shift reminders, QR scanner placement, etc."
                    ></textarea>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Gate-specific Settings</h2>
                    <p class="text-sm text-slate-500">Fine-tune how this gate behaves for guards and alerts.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4">
                        <input
                            v-model="form.settings.requires_incident_report"
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span>
                            <span class="font-semibold text-slate-900 block">Require incident report</span>
                            <span class="text-sm text-slate-500">
                                Guard must log an incident summary before ending shifts at this gate.
                            </span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4">
                        <input
                            v-model="form.settings.auto_notify_admin"
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span>
                            <span class="font-semibold text-slate-900 block">Auto notify admins</span>
                            <span class="text-sm text-slate-500">
                                Send push/email alerts when warnings or failures happen at this gate.
                            </span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4">
                        <input
                            v-model="form.settings.allow_manual_entry"
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span>
                            <span class="font-semibold text-slate-900 block">Allow manual PIN entry</span>
                            <span class="text-sm text-slate-500">
                                Disable if this gate should only use QR scanning.
                            </span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4">
                        <input
                            v-model="form.settings.enforce_device_lock"
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span>
                            <span class="font-semibold text-slate-900 block">Enforce device lock</span>
                            <span class="text-sm text-slate-500">
                                Guards must register their device ID before scanning.
                            </span>
                        </span>
                    </label>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Max scans per minute</label>
                        <input
                            v-model.number="form.settings.max_scan_per_minute"
                            type="number"
                            min="10"
                            max="300"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                        />
                        <p class="text-xs text-slate-500 mt-1">
                            Used for duplicate-scan suppression alerts.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Guard instructions</label>
                        <textarea
                            v-model="form.settings.guard_instructions"
                            rows="3"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                            placeholder="Remind guards about delivery lanes, visitor parking, etc."
                        ></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <Link
                    :href="route('gates.index')"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-400"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/40 transition hover:bg-blue-500 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Save Gate
                </button>
            </div>
        </form>

        <ConfirmModal
            v-model="showConfirm"
            title="Register this gate?"
            confirm-label="Create Gate"
            processing-label="Saving..."
            :loading="form.processing"
            @confirm="submit"
        >
            <p>
                Create <span class="font-semibold">{{ form.name || 'a new gate' }}</span>
                for <span class="font-semibold">{{ selectedSubdivisionName }}</span>.
            </p>
            <p class="mt-2 text-sm text-gray-600">
                These settings will define how guards scan and log entries for this gate.
            </p>
        </ConfirmModal>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    statusOptions: { type: Array, default: () => [] },
    typeOptions: { type: Array, default: () => [] },
    subdivisionOptions: { type: Array, default: () => [] },
    defaultSettings: {
        type: Object,
        default: () => ({
            requires_incident_report: false,
            auto_notify_admin: false,
            allow_manual_entry: true,
            enforce_device_lock: false,
            max_scan_per_minute: 60,
            guard_instructions: '',
        }),
    },
});

const form = useForm({
    subdivision_id: '',
    name: '',
    code: '',
    location: '',
    type: props.typeOptions[0] ?? 'both',
    status: props.statusOptions[0] ?? 'active',
    notes: '',
    coordinates: {
        lat: '',
        lng: '',
    },
    settings: { ...props.defaultSettings },
});

const showConfirm = ref(false);

const selectedSubdivisionName = computed(() => {
    if (!form.subdivision_id) return 'this subdivision';
    return props.subdivisionOptions.find((opt) => opt.id === form.subdivision_id)?.name ?? 'this subdivision';
});

const submit = () => {
    form.post(route('gates.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showConfirm.value = false;
        },
    });
};

const openConfirm = () => {
    showConfirm.value = true;
};
</script>
