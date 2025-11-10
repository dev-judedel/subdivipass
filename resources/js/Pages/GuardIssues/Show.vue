<template>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white border-b">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Issue Report</p>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">#{{ issue.id }} · {{ formatIssue(issue.issue_type) }}</h1>
                </div>
                <Link :href="route('guard-issues.index')" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                    Back to list
                </Link>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase text-slate-500">Status</p>
                            <p class="text-lg font-semibold text-slate-900 capitalize">{{ issue.status.replace('_', ' ') }}</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full" :class="statusClass(issue.status)">
                            {{ issue.status }}
                        </span>
                    </div>
                    <div class="grid gap-4 text-sm text-slate-700">
                        <p><span class="text-slate-500">Severity:</span> <span class="capitalize font-semibold">{{ issue.severity }}</span></p>
                        <p><span class="text-slate-500">Guard:</span> {{ issue.guard_user?.name || '—' }}</p>
                        <p><span class="text-slate-500">Gate:</span> {{ issue.gate?.name || '—' }}</p>
                        <p><span class="text-slate-500">Pass:</span> {{ issue.pass?.pass_number || 'N/A' }} ({{ issue.pass?.visitor_name || 'Unknown' }})</p>
                        <p><span class="text-slate-500">Reported:</span> {{ formatDate(issue.created_at) }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <h2 class="text-sm uppercase tracking-wide text-slate-500 mb-3">Update Status</h2>
                    <form class="space-y-4" @submit.prevent="submit">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Status</label>
                            <select v-model="form.status" class="w-full rounded-lg border-slate-300 text-sm">
                                <option v-for="value in statuses" :key="value" :value="value">
                                    {{ value.replace('_', ' ') }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Resolution Notes</label>
                            <textarea
                                v-model="form.resolution_notes"
                                rows="4"
                                class="w-full rounded-lg border-slate-300 text-sm"
                                placeholder="Add notes or actions taken..."
                            ></textarea>
                        </div>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            Save Changes
                        </button>
                        <p v-if="successMessage" class="text-xs text-green-600">
                            {{ successMessage }}
                        </p>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-sm uppercase tracking-wide text-slate-500">Description</h2>
                <p class="text-sm text-slate-800 whitespace-pre-line">{{ issue.description }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    issue: { type: Object, required: true },
    statuses: { type: Array, default: () => [] },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const form = useForm({
    status: props.issue.status,
    resolution_notes: props.issue.resolution_notes || '',
});

const submit = () => {
    form.put(route('guard-issues.update', props.issue.id));
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

const formatIssue = (value) => {
    return value
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
};

const formatIssue = (value) => {
    return value
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
};

const formatDate = (value) => {
    if (!value) {
        return '';
    }
    return new Date(value).toLocaleString();
};
</script>
