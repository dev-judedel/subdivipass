<template>
    <div class="space-y-8">
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="card in statCards"
                :key="card.label"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ card.label }}</p>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ card.value }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ card.helper }}</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Pass volume (last 7 days)</h2>
                        <p class="text-xs text-slate-500">All subdivisions combined</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">
                        Total {{ totalPassesByDay }}
                    </span>
                </div>
                <div class="flex h-48 items-end gap-3">
                    <div
                        v-for="day in passesByDay"
                        :key="day.day"
                        class="flex flex-1 flex-col items-center gap-2"
                    >
                        <div
                            class="w-8 rounded-t-full bg-gradient-to-b from-blue-500 to-blue-300"
                            :style="{ height: `${computeBarHeight(day.total)}%` }"
                        ></div>
                        <span class="text-xs text-slate-500">{{ day.day }}</span>
                    </div>
                    <p v-if="!passesByDay.length" class="text-sm text-slate-500">No data available.</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Guard alerts</h2>
                    <Link
                        v-if="guardAlerts.length"
                        :href="route('guard-issues.index')"
                        class="text-xs font-semibold text-blue-600 hover:text-blue-500"
                    >
                        View all
                    </Link>
                </div>
                <p v-if="!guardAlerts.length" class="text-sm text-slate-500">No incidents reported today.</p>
                <ul v-else class="space-y-3">
                    <li
                        v-for="alert in guardAlerts"
                        :key="alert.id"
                        class="rounded-xl border border-slate-100 p-3"
                    >
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold capitalize">{{ formatIssue(alert.type) }}</span>
                            <span class="text-xs text-slate-400">{{ alert.created_at }}</span>
                        </div>
                        <p class="text-xs text-slate-500">
                            Severity:
                            <span :class="severityClass(alert.severity)" class="font-semibold">
                                {{ alert.severity }}
                            </span>
                        </p>
                    </li>
                </ul>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Pending approval queue</h2>
                        <p class="text-xs text-slate-500">Newest pass requests needing action.</p>
                    </div>
                    <Link
                        :href="route('passes.index', { status: 'pending' })"
                        class="text-xs font-semibold text-blue-600 hover:text-blue-500"
                    >
                        Manage queue
                    </Link>
                </div>
                <p v-if="!pendingApprovals.length" class="text-sm text-slate-500">No pending passes right now.</p>
                <ul v-else class="space-y-3">
                    <li
                        v-for="item in pendingApprovals"
                        :key="item.id"
                        class="flex flex-col gap-2 rounded-2xl border border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ item.visitor_name }} · {{ item.pass_number }}
                            </p>
                            <p class="text-xs text-slate-500">{{ item.type }} · {{ item.subdivision }}</p>
                            <p class="text-xs text-slate-400">
                                Requested by {{ item.requested_by }} • {{ formatTimestamp(item.created_at) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('passes.show', item.id)"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-500"
                            >
                                Review
                            </Link>
                            <Link
                                v-if="hasApprovalRole"
                                :href="route('guard.passes.approve', item.id)"
                                method="post"
                                as="button"
                                class="rounded-lg bg-emerald-500 px-3 py-1 text-xs font-semibold text-white shadow hover:bg-emerald-400"
                            >
                                Approve
                            </Link>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Recent activity</h2>
                </div>
                <p v-if="!recentActivity.length" class="text-sm text-slate-500">No recent events.</p>
                <ul v-else class="space-y-3">
                    <li
                        v-for="event in recentActivity"
                        :key="event.id"
                        class="rounded-xl border border-slate-100 p-3"
                    >
                        <p class="text-sm font-semibold text-slate-900">
                            {{ formatAction(event.action) }}
                            <span class="text-xs text-slate-400">#{{ event.pass_number }}</span>
                        </p>
                        <p class="text-xs text-slate-500">{{ event.description }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ event.logged_at }}</p>
                    </li>
                </ul>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Link
                :href="route('passes.create')"
                class="rounded-2xl border border-blue-100 bg-blue-50 p-4 shadow-sm hover:border-blue-200"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-500">Quick action</p>
                <p class="mt-2 text-lg font-semibold text-blue-900">Create pass</p>
                <p class="text-xs text-blue-600 mt-1">Visitor, delivery, job order</p>
            </Link>
            <Link
                :href="route('passes.index', { status: 'pending' })"
                class="rounded-2xl border border-purple-100 bg-purple-50 p-4 shadow-sm hover:border-purple-200"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-purple-500">Quick action</p>
                <p class="mt-2 text-lg font-semibold text-purple-900">Approval queue</p>
                <p class="text-xs text-purple-600 mt-1">Bulk approve or reject</p>
            </Link>
            <Link
                :href="route('guard-issues.index')"
                class="rounded-2xl border border-rose-100 bg-rose-50 p-4 shadow-sm hover:border-rose-200"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-rose-500">Quick action</p>
                <p class="mt-2 text-lg font-semibold text-rose-900">Guard alerts</p>
                <p class="text-xs text-rose-600 mt-1">Investigate incidents</p>
            </Link>
            <Link
                :href="route('gates.index')"
                class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm hover:border-emerald-200"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-500">Quick action</p>
                <p class="mt-2 text-lg font-semibold text-emerald-900">Gate settings</p>
                <p class="text-xs text-emerald-600 mt-1">Update guard policies</p>
            </Link>
        </section>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    pendingApprovals: { type: Array, default: () => [] },
    recentActivity: { type: Array, default: () => [] },
    passesByDay: { type: Array, default: () => [] },
    guardAlerts: { type: Array, default: () => [] },
});

const page = usePage();

const hasApprovalRole = computed(() => {
    const roles = page.props.auth.user.roles.map((role) => role.name);
    return roles.includes('admin') || roles.includes('super-admin');
});

const statCards = computed(() => [
    {
        label: 'Total passes',
        value: props.stats.total_passes ?? 0,
        helper: 'All-time total',
    },
    {
        label: 'Active passes',
        value: props.stats.active_passes ?? 0,
        helper: 'Currently valid',
    },
    {
        label: 'Pending approvals',
        value: props.stats.pending_approvals ?? 0,
        helper: 'Needs review',
    },
    {
        label: 'Expiring today',
        value: props.stats.expiring_today ?? 0,
        helper: 'Expires in 24h',
    },
]);

const chartMax = computed(() => Math.max(...props.passesByDay.map((day) => day.total), 1));
const totalPassesByDay = computed(() => props.passesByDay.reduce((sum, day) => sum + day.total, 0));

const computeBarHeight = (value) => (value / chartMax.value) * 100 || 0;

const severityClass = (severity) => {
    switch (severity) {
        case 'high':
            return 'text-red-600';
        case 'medium':
            return 'text-amber-600';
        default:
            return 'text-slate-500';
    }
};

const formatIssue = (value) => value?.replace(/_/g, ' ') ?? 'issue';
const formatAction = (value) => value?.replace(/_/g, ' ') ?? 'update';
const formatTimestamp = (value) => (value ? new Date(value).toLocaleString() : 'just now');
</script>
