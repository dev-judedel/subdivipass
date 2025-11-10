<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
                <p class="mt-1 text-sm text-gray-500">Manage system users and their roles</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Test: Display user count -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Backend Test</h2>
                <div class="space-y-2">
                    <p class="text-sm text-gray-600">✅ Route is working!</p>
                    <p class="text-sm text-gray-600">✅ Authorization passed!</p>
                    <p class="text-sm text-gray-600">✅ Total users: {{ users.total }}</p>
                    <p class="text-sm text-gray-600">✅ Current page: {{ users.current_page }}</p>
                </div>
            </div>

            <!-- Simple user list -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="user in users.data" :key="user.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ user.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ user.email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span v-for="role in user.roles" :key="role.id" class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ role.name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span :class="getStatusClass(user.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ user.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination info -->
                <div class="bg-white px-4 py-3 border-t border-gray-200">
                    <p class="text-sm text-gray-700">
                        Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Props
const props = defineProps({
    users: Object,
    roles: Array,
    subdivisions: Array,
    filters: Object,
});

// Methods
const getStatusClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-gray-100 text-gray-800',
        suspended: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
