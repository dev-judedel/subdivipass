<template>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white shadow">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Subdivision Setup</p>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">Update {{ subdivision.name }}</h1>
                    <p class="text-sm text-gray-500 mt-1">Adjust branding, contact info, and guard rules.</p>
                </div>
                <Link
                    :href="route('subdivisions.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400"
                >
                    Back to list
                </Link>
            </div>
        </div>

        <form class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" @submit.prevent="submit">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Subdivision Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Code</label>
                        <input
                            v-model="form.code"
                            type="text"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm uppercase tracking-wide"
                            :class="{ 'border-red-500': form.errors.code }"
                        />
                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                            {{ form.errors.code }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Address</label>
                    <textarea
                        v-model="form.address"
                        rows="3"
                        class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                        :class="{ 'border-red-500': form.errors.address }"
                    ></textarea>
                    <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">
                        {{ form.errors.address }}
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Primary Contact</label>
                        <input
                            v-model="form.contact_person"
                            type="text"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                            :class="{ 'border-red-500': form.errors.contact_person }"
                        />
                        <p v-if="form.errors.contact_person" class="mt-1 text-sm text-red-600">
                            {{ form.errors.contact_person }}
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Email</label>
                            <input
                                v-model="form.contact_email"
                                type="email"
                                class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                                :class="{ 'border-red-500': form.errors.contact_email }"
                            />
                            <p v-if="form.errors.contact_email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.contact_email }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Phone</label>
                            <input
                                v-model="form.contact_phone"
                                type="text"
                                class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                                :class="{ 'border-red-500': form.errors.contact_phone }"
                            />
                            <p v-if="form.errors.contact_phone" class="mt-1 text-sm text-red-600">
                                {{ form.errors.contact_phone }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
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
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Logo</label>
                        <input
                            ref="logoInput"
                            type="file"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                            @change="handleLogoChange"
                        />
                        <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">
                            {{ form.errors.logo }}
                        </p>
                        <div class="mt-3 flex items-center gap-4">
                            <img
                                v-if="logoPreview"
                                :src="logoPreview"
                                alt="Logo preview"
                                class="h-16 rounded-xl border border-slate-200 object-cover"
                            />
                            <p v-else class="text-xs text-slate-500">No logo uploaded.</p>
                            <button
                                v-if="subdivision.logo_url || logoPreview"
                                type="button"
                                class="text-xs font-semibold text-red-500"
                                @click="clearLogo"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Notes</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                    ></textarea>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-slate-900">Guard & Pass Settings</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 p-4 space-y-4">
                        <label class="flex items-start gap-3">
                            <input
                                v-model="form.settings.requires_approval"
                                type="checkbox"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span>
                                <span class="font-semibold text-slate-900 block">Require pass approval</span>
                                <span class="text-sm text-slate-500">Keep passes pending until an admin reviews.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3">
                            <input
                                v-model="form.settings.allow_manual_entry"
                                type="checkbox"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span>
                                <span class="font-semibold text-slate-900 block">Allow manual PIN entry</span>
                                <span class="text-sm text-slate-500">Fallback validation for QR failures.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3">
                            <input
                                v-model="form.settings.send_guard_alerts"
                                type="checkbox"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span>
                                <span class="font-semibold text-slate-900 block">Send guard broadcast alerts</span>
                                <span class="text-sm text-slate-500">Push important notices to guard devices.</span>
                            </span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Default pass validity (hours)</label>
                        <input
                            v-model.number="form.settings.default_pass_validity_hours"
                            type="number"
                            min="1"
                            max="8760"
                            class="mt-1 w-full rounded-xl border px-4 py-2.5 text-sm"
                            :class="{ 'border-red-500': form.errors['settings.default_pass_validity_hours'] }"
                        />
                        <p v-if="form.errors['settings.default_pass_validity_hours']" class="mt-1 text-sm text-red-600">
                            {{ form.errors['settings.default_pass_validity_hours'] }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1">Used when creating new passes for this subdivision.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <Link
                    :href="route('subdivisions.index')"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-400"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/40 transition hover:bg-blue-500 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    subdivision: { type: Object, required: true },
    statusOptions: { type: Array, default: () => [] },
});

const form = useForm({
    _method: 'put',
    name: props.subdivision.name,
    code: props.subdivision.code,
    address: props.subdivision.address,
    contact_person: props.subdivision.contact_person,
    contact_email: props.subdivision.contact_email,
    contact_phone: props.subdivision.contact_phone,
    status: props.subdivision.status,
    notes: props.subdivision.notes,
    settings: { ...props.subdivision.settings },
    logo: null,
    remove_logo: false,
});

const logoInput = ref(null);
const logoPreview = ref(props.subdivision.logo_url);

const handleLogoChange = (event) => {
    const file = event.target.files[0];
    form.logo = file || null;
    if (file) {
        form.remove_logo = false;
    }

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result ?? null;
        };
        reader.readAsDataURL(file);
    }
};

const clearLogo = () => {
    form.logo = null;
    logoPreview.value = null;
    form.remove_logo = true;
    if (logoInput.value) {
        logoInput.value.value = '';
    }
};

const submit = () => {
    form.post(route('subdivisions.update', props.subdivision.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>
