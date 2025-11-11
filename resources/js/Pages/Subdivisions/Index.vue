<template>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Operations</p>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">Subdivision Directory</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage communities, gate assignments, and guard policies.</p>
                </div>
                <Link
                    :href="route('subdivisions.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/40 transition hover:bg-blue-500"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                    </svg>
                    New Subdivision
                </Link>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Search</label>
                        <div class="relative">
                            <input
                                v-model="filterForm.search"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Name, code, contact..."
                            />
                            <svg
                                class="w-4 h-4 text-slate-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-4.35-4.35M16 10A6 6 0 1 1 4 10a6 6 0 0 1 12 0Z"
                                />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Status</label>
                        <select
                            v-model="filterForm.status"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">All statuses</option>
                            <option v-for="status in statusOptions" :key="status" :value="status" class="capitalize">
                                {{ status }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="divide-y divide-slate-100">
                    <div
                        v-for="subdivision in subdivisions.data"
                        :key="subdivision.id"
                        class="p-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center">
                                <img
                                    v-if="subdivision.logo_url"
                                    :src="subdivision.logo_url"
                                    :alt="subdivision.name"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-lg font-semibold text-slate-500">
                                    {{ subdivision.name.slice(0, 2).toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <div class="flex items-center gap-3">
                                    <h2 class="text-lg font-semibold text-slate-900">
                                        {{ subdivision.name }}
                                    </h2>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize"
                                        :class="statusBadge(subdivision.status)"
                                    >
                                        {{ subdivision.status }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500 mt-1">Code: {{ subdivision.code }}</p>
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ subdivision.address || 'No address provided' }}
                                </p>
                                <p v-if="subdivision.contact_person" class="text-xs text-slate-400 mt-1">
                                    Contact: {{ subdivision.contact_person }} • {{ subdivision.contact_email || 'No email' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                            <div class="text-center">
                                <p class="text-xl font-semibold text-slate-900">{{ subdivision.gates_count }}</p>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Gates</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xl font-semibold text-slate-900">{{ subdivision.pass_types_count }}</p>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Pass types</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xl font-semibold text-slate-900">{{ subdivision.passes_count }}</p>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Passes</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link
                                :href="route('subdivisions.edit', subdivision.id)"
                                class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-400 hover:text-blue-600"
                            >
                                Manage
                            </Link>
                        </div>
                    </div>
                    <p v-if="!subdivisions.data.length" class="p-8 text-center text-sm text-slate-500">
                        No subdivisions match the current filters.
                    </p>
                </div>
                <div v-if="subdivisions.links.length > 3" class="border-t border-slate-100 bg-slate-50 px-4 py-3 flex flex-wrap gap-2 justify-end">
                    <Link
                        v-for="link in subdivisions.links"
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
    subdivisions: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total: 0, active: 0, inactive: 0, suspended: 0 }) },
});

const filterForm = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
});

const applyFilters = () => {
    router.get(route('subdivisions.index'), filterForm, {
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
        case 'inactive':
            return 'bg-slate-100 text-slate-600';
        default:
            return 'bg-yellow-100 text-yellow-700';
    }
};

const statCards = computed(() => [
    { label: 'Total', value: props.stats.total, hint: 'Registered subdivisions' },
    { label: 'Active', value: props.stats.active, hint: 'Accepting visitors' },
    { label: 'Inactive', value: props.stats.inactive, hint: 'Temporarily paused' },
    { label: 'Suspended', value: props.stats.suspended, hint: 'Requires admin review' },
]);
</script>
