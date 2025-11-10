<template>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Security Operations</p>
                        <h1 class="text-3xl font-bold text-gray-900 mt-1">Guard Issue Reports</h1>
                        <p class="mt-1 text-sm text-gray-500">Monitor guard-submitted incidents and respond quickly.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Open</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ stats.open }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">In Progress</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ stats.in_progress }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Resolved</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ stats.resolved }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Status</label>
                        <select v-model="filterForm.status" class="w-full rounded-lg border-slate-300 text-sm" @change="applyFilters">
                            <option value="">All</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Severity</label>
                        <select v-model="filterForm.severity" class="w-full rounded-lg border-slate-300 text-sm" @change="applyFilters">
                            <option value="">All</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Search</label>
                        <input
                            v-model="filterForm.search"
                            type="text"
                            class="w-full rounded-lg border-slate-300 text-sm"
                            placeholder="Description, guard name, pass #"
                            @input="debouncedSearch"
                        />
                    </div>
                </div>
                <div class="flex justify-end">
                    <button
                        type="button"
                        class="text-sm font-semibold text-blue-600 hover:text-blue-700"
                        @click="clearFilters"
                    >
                        Clear filters
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Issue</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Guard</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Gate</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Severity</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="issue in issues.data" :key="issue.id">
                            <td class="px-6 py-4 text-sm text-slate-800">
                                <p class="font-semibold">{{ formatIssue(issue.issue_type) }}</p>
                                <p class="text-xs text-slate-500 line-clamp-2">{{ issue.description }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ issue.guard_user?.name || '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ issue.gate?.name || '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full" :class="statusClass(issue.status)">
                                    {{ issue.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700 capitalize">
                                {{ issue.severity }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <Link :href="route('guard-issues.show', issue.id)" class="text-blue-600 hover:text-blue-800 font-semibold">
                                    View
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!issues.data.length">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">
                                No issue reports found.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t border-slate-200 px-6 py-4">
                    <p class="text-sm text-slate-500">
                        Showing {{ issues.from || 0 }} to {{ issues.to || 0 }} of {{ issues.total || 0 }} reports
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="link in issues.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-3 py-1 rounded border text-sm"
                            :class="[
                                link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-300 text-slate-600 hover:bg-slate-50',
                                !link.url && 'pointer-events-none text-slate-400 bg-slate-100'
                            ]"
                            preserve-scroll
                            preserve-state
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    issues: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({ open: 0, in_progress: 0, resolved: 0 }) },
});

const filterForm = reactive({
    status: props.filters.status || '',
    severity: props.filters.severity || '',
    search: props.filters.search || '',
});

const applyFilters = () => {
    router.get(route('guard-issues.index'), filterForm, {
        preserveState: true,
        replace: true,
    });
};

let timer = null;
const debouncedSearch = () => {
    clearTimeout(timer);
    timer = setTimeout(() => applyFilters(), 400);
};

const clearFilters = () => {
    filterForm.status = '';
    filterForm.severity = '';
    filterForm.search = '';
    applyFilters();
};

const formatIssue = (value) => {
    return value
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
};

const statusClass = (status) => {
    switch (status) {
        case 'open':
            return 'bg-red-100 text-red-700';
        case 'in_progress':
            return 'bg-yellow-100 text-yellow-700';
        default:
            return 'bg-green-100 text-green-700';
    }
};
</script>
