<template>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Gate Configuration</p>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">Gate Directory</h1>
                    <p class="text-sm text-gray-500 mt-1">Track physical gates, guard posts, and maintenance states.</p>
                </div>
                <Link
                    :href="route('gates.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/40 transition hover:bg-blue-500"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                    </svg>
                    New Gate
                </Link>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div
                    v-for="card in statCards"
                    :key="card.label"
                    class="rounded-2xl border border-slate-200 bg-white p-4"
                >
                    <p class="text-xs uppercase tracking-wide text-slate-500">{{ card.label }}</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ card.value }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ card.hint }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Search</label>
                        <input
                            v-model="filterForm.search"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Name, code, location..."
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Subdivision</label>
                        <select
                            v-model="filterForm.subdivision_id"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm"
                        >
                            <option value="">All</option>
                            <option v-for="option in subdivisionOptions" :key="option.id" :value="option.id">
                                {{ option.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Type</label>
                        <select
                            v-model="filterForm.type"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm capitalize"
                        >
                            <option value="">All</option>
                            <option v-for="type in typeOptions" :key="type" :value="type">
                                {{ type }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Status</label>
                        <select
                            v-model="filterForm.status"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm capitalize"
                        >
                            <option value="">All</option>
                            <option v-for="status in statusOptions" :key="status" :value="status">
                                {{ status }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="divide-y divide-slate-100">
                    <div
                        v-for="gate in gates.data"
                        :key="gate.id"
                        class="p-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-semibold text-slate-900">{{ gate.name }}</h2>
                                <span class="text-xs uppercase tracking-wide text-slate-400">#{{ gate.code }}</span>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">
                                {{ gate.location || 'No location description' }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                {{ gate.subdivision?.name || 'Unassigned subdivision' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize" :class="typeBadge(gate.type)">
                                {{ gate.type }}
                            </span>
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold capitalize"
                                :class="statusBadge(gate.status)"
                            >
                                {{ gate.status }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold text-slate-600 bg-slate-100">
                                {{ gate.guards_count }} Guards
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link
                                :href="route('gates.edit', gate.id)"
                                class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-400 hover:text-blue-600"
                            >
                                Configure
                            </Link>
                        </div>
                    </div>
                    <p v-if="!gates.data.length" class="p-8 text-center text-sm text-slate-500">
                        No gates match the current filters.
                    </p>
                </div>
                <div v-if="gates.links.length > 3" class="border-t border-slate-100 bg-slate-50 px-4 py-3 flex flex-wrap gap-2 justify-end">
                    <Link
                        v-for="link in gates.links"
                        :key="link.url ?? link.label"
                        :href="link.url || '#'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium"
                        :class="[
                            link.active
                                ? 'bg-blue-600 text-white shadow-sm'
                                : link.url
                                    ? 'text-slate-600 hover:bg-white hover:text-slate-900'
                                    : 'text-slate-400 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { reactive, watch, computed, onBeforeUnmount } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    gates: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
    typeOptions: { type: Array, default: () => [] },
    subdivisionOptions: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total: 0, active: 0, maintenance: 0 }) },
});

const filterForm = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    subdivision_id: props.filters.subdivision_id ?? '',
    type: props.filters.type ?? '',
});

const applyFilters = () => {
    router.get(route('gates.index'), filterForm, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

let filterTimeout;
watch(
    filterForm,
    () => {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(applyFilters, 300);
    },
    { deep: true }
);

onBeforeUnmount(() => {
    clearTimeout(filterTimeout);
});

const statusBadge = (status) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-700';
        case 'maintenance':
            return 'bg-yellow-100 text-yellow-700';
        default:
            return 'bg-slate-100 text-slate-600';
    }
};

const typeBadge = (type) => {
    switch (type) {
        case 'entry':
            return 'bg-blue-100 text-blue-700';
        case 'exit':
            return 'bg-purple-100 text-purple-700';
        default:
            return 'bg-slate-100 text-slate-600';
    }
};

const statCards = computed(() => [
    { label: 'Total Gates', value: props.stats.total, hint: 'All registered gates' },
    { label: 'Active', value: props.stats.active, hint: 'Operational' },
    { label: 'Maintenance', value: props.stats.maintenance, hint: 'Needs attention' },
]);
</script>
