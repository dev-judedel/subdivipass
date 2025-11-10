<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-blue-600">SubdiPass</h1>
                        </div>
                        <div class="hidden sm:ml-10 sm:flex sm:space-x-6">
                            <Link
                                v-for="item in visibleNavigation"
                                :key="item.label"
                                :href="item.href"
                                :class="[
                                    item.active
                                        ? 'border-blue-600 text-gray-900'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium'
                                ]"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ user?.name }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ primaryRole }}</p>
                        </div>
                        <form method="POST" action="/logout" @submit.prevent="logout">
                            <button type="submit" class="text-sm text-gray-700 hover:text-gray-900">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const userRoles = computed(() => user.value?.roles?.map(role => role.name) ?? []);
const hasRole = (roles) => roles.some(role => userRoles.value.includes(role));

const navigation = computed(() => [
    {
        label: 'Dashboard',
        href: route('dashboard'),
        active: page.url === '/dashboard',
        show: true,
    },
    {
        label: 'Passes',
        href: route('passes.index'),
        active: page.url.startsWith('/passes'),
        show: hasRole(['employee', 'admin', 'super-admin']),
    },
    {
        label: 'Pass Types',
        href: route('pass-types.index'),
        active: page.url.startsWith('/pass-types'),
        show: hasRole(['admin', 'super-admin']),
    },
    {
        label: 'Users',
        href: route('users.index'),
        active: page.url.startsWith('/users'),
        show: hasRole(['admin', 'super-admin']),
    },
    {
        label: 'Roles',
        href: route('roles.index'),
        active: page.url.startsWith('/roles'),
        show: hasRole(['admin', 'super-admin']),
    },
]);

const visibleNavigation = computed(() => navigation.value.filter(item => item.show));

const primaryRole = computed(() => userRoles.value[0] ?? 'user');

const logout = () => {
    router.post('/logout');
};
</script>
