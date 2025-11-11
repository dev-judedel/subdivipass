<template>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white shadow">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Gate Configuration</p>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">Edit {{ gate.name }}</h1>
                    <p class="text-sm text-gray-500 mt-1">Update guard access and operational status.</p>
                </div>
                <Link
                    :href="route('gates.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400"
                >
                    Back to list
                </Link>
            </div>
        </div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <form class="space-y-6" @submit.prevent="submit">
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
                    ></textarea>
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
                    Save Changes
                </button>
            </div>
            </form>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Assigned Guards</h2>
                        <p class="text-sm text-slate-500">Only these guards will see {{ gate.name }} in their scanner.</p>
                    </div>
                    <form class="flex flex-col gap-3 sm:flex-row sm:items-center" @submit.prevent="assignGuard">
                        <select
                            v-model="guardAssignmentForm.user_id"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm min-w-[220px]"
                        >
                            <option value="">Select guard</option>
                            <option v-for="guard in availableGuards" :key="guard.id" :value="guard.id">
                                {{ guard.name }} ({{ guard.email }})
                            </option>
                        </select>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-blue-600/30 hover:bg-blue-500 disabled:opacity-60"
                            :disabled="guardAssignmentForm.processing || !guardAssignmentForm.user_id"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                            </svg>
                            Assign
                        </button>
                    </form>
                </div>
                <div v-if="assignedGuards.length" class="divide-y divide-slate-100 rounded-2xl border border-slate-100">
                    <div
                        v-for="guard in assignedGuards"
                        :key="guard.id"
                        class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ guard.name }}</p>
                            <p class="text-xs text-slate-500">{{ guard.email }}</p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-full border border-slate-200 p-2 text-slate-500 transition hover:border-red-400 hover:text-red-500"
                            @click="removeGuard(guard.id)"
                        >
                            <span class="sr-only">Remove {{ guard.name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500">No guards assigned to this gate.</p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Recent Activity</h2>
                        <p class="text-sm text-slate-500">Latest scans synced for this gate.</p>
                    </div>
                </div>
                <div v-if="recentActivity.length" class="space-y-4">
                    <div
                        v-for="scan in recentActivity"
                        :key="scan.id"
                        class="rounded-xl border border-slate-100 bg-slate-50/70 p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ scan.pass_number || 'Unknown pass' }}
                                <span class="ml-2 text-xs text-slate-500">{{ scan.visitor_name || 'No visitor info' }}</span>
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ scan.scan_type }} via {{ scan.scan_method }} • {{ scan.scanned_at }}
                            </p>
                            <p v-if="scan.was_offline" class="text-xs text-amber-600">Synced from offline queue</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize" :class="resultBadge(scan.result)">
                            {{ scan.result }}
                        </span>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500">No scans recorded for this gate yet.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    gate: { type: Object, required: true },
    statusOptions: { type: Array, default: () => [] },
    typeOptions: { type: Array, default: () => [] },
    subdivisionOptions: { type: Array, default: () => [] },
    assignedGuards: { type: Array, default: () => [] },
    availableGuards: { type: Array, default: () => [] },
    recentActivity: { type: Array, default: () => [] },
});

const form = useForm({
    _method: 'put',
    subdivision_id: props.gate.subdivision_id,
    name: props.gate.name,
    code: props.gate.code,
    location: props.gate.location,
    type: props.gate.type,
    status: props.gate.status,
    notes: props.gate.notes,
    coordinates: {
        lat: props.gate.coordinates?.lat ?? '',
        lng: props.gate.coordinates?.lng ?? '',
    },
});

const submit = () => {
    form.post(route('gates.update', props.gate.id), {
        preserveScroll: true,
    });
};

const guardAssignmentForm = useForm({
    user_id: '',
});

const assignGuard = () => {
    if (!guardAssignmentForm.user_id) {
        return;
    }
    guardAssignmentForm.post(route('gates.guards.store', props.gate.id), {
        preserveScroll: true,
        onSuccess: () => guardAssignmentForm.reset('user_id'),
    });
};

const removeGuard = (userId) => {
    router.delete(route('gates.guards.destroy', [props.gate.id, userId]), {
        preserveScroll: true,
    });
};

const resultBadge = (result) => {
    switch (result) {
        case 'success':
            return 'bg-green-100 text-green-700';
        case 'warning':
            return 'bg-yellow-100 text-yellow-700';
        default:
            return 'bg-red-100 text-red-700';
    }
};
</script>
