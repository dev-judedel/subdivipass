<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Administration</p>
                <h1 class="text-3xl font-bold text-gray-900 mt-1">Role Activity Logs</h1>
                <p class="text-sm text-gray-500 mt-1">Recent changes made to roles and permissions.</p>
            </div>
            <Link
                :href="route('roles.index')"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Back to Roles</span>
            </Link>
        </div>

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600">Timestamp</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600">Role</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600">Event</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600">Description</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600">Actor</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <tr v-if="!logs.data.length">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No role activity recorded yet.
                        </td>
                    </tr>
                    <tr v-for="log in logs.data" :key="log.id">
                        <td class="px-6 py-4 text-gray-600">{{ formatDate(log.created_at) }}</td>
                        <td class="px-6 py-4 text-gray-900 capitalize">
                            {{ log.subject?.name || '—' }}
                        </td>
                        <td class="px-6 py-4 text-gray-700">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 uppercase tracking-wide">
                                {{ log.event || 'update' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ log.description || '—' }}
                        </td>
                        <td class="px-6 py-4 text-gray-700">
                            {{ log.causer?.name || 'System' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t border-gray-200 px-6 py-4">
                <p class="text-sm text-gray-500">
                    Showing {{ logs.from || 0 }} to {{ logs.to || 0 }} of {{ logs.total || 0 }} entries
                </p>
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="link in logs.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1 rounded border text-sm"
                        :class="[
                            link.active
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'border-gray-300 text-gray-600 hover:bg-gray-50',
                            !link.url && 'pointer-events-none text-gray-400 bg-gray-100'
                        ]"
                        preserve-scroll
                        preserve-state
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    logs: { type: Object, required: true },
});

const formatDate = (value) => {
    if (!value) {
        return '';
    }

    return new Date(value).toLocaleString();
};
</script>
