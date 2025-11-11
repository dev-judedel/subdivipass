<template>
    <div class="min-h-screen bg-slate-950 text-white flex flex-col">
        <header class="bg-slate-900/80 border-b border-slate-800 backdrop-blur">
            <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400">Guard Panel</p>
                    <h1 class="text-xl font-bold tracking-tight">SubdiPass</h1>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold">{{ guard?.name }}</p>
                    <p class="text-xs text-slate-400 capitalize">{{ guardPrimaryRole }}</p>
                    <form method="POST" action="/logout" @submit.prevent="logout">
                        <button
                            type="submit"
                            class="mt-2 text-xs font-semibold text-red-300 hover:text-red-100"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-6">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const guard = computed(() => page.props.auth?.user ?? null);
const guardPrimaryRole = computed(() => guard.value?.roles?.[0]?.name ?? 'guard');

const logout = () => {
    router.post('/logout', {}, { onFinish: () => (window.location.href = '/login') });
};
</script>
