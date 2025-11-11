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

        <form class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" @submit.prevent="submit">
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
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    statusOptions: { type: Array, default: () => [] },
    typeOptions: { type: Array, default: () => [] },
    subdivisionOptions: { type: Array, default: () => [] },
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
});

const submit = () => {
    form.post(route('gates.store'), {
        preserveScroll: true,
    });
};
</script>
