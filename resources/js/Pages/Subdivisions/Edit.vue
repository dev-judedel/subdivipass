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

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <p class="text-xs uppercase tracking-wide text-slate-400 mb-4">Operational Snapshot</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div
                        v-for="card in metricCards"
                        :key="card.label"
                        class="rounded-xl border border-slate-100 bg-slate-50/70 p-4"
                    >
                        <p class="text-sm text-slate-500">{{ card.label }}</p>
                        <p class="text-3xl font-bold text-slate-900 mt-1">{{ card.value }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ card.hint }}</p>
                    </div>
                </div>
            </div>

            <form class="space-y-6" @submit.prevent="openMainConfirm">
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

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Assigned Team</h2>
                        <p class="text-sm text-slate-500">Control which admins, employees, or guards can manage this subdivision.</p>
                    </div>
                    <form class="flex flex-col gap-3 sm:flex-row sm:items-center" @submit.prevent="openAssignConfirm">
                        <select
                            v-model="assignmentForm.user_id"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm min-w-[220px]"
                        >
                            <option value="">Select user</option>
                            <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.role || 'user' }})
                            </option>
                        </select>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-blue-600/30 hover:bg-blue-500 disabled:opacity-60"
                            :disabled="assignmentForm.processing || !assignmentForm.user_id"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                            </svg>
                            Assign
                        </button>
                    </form>
                </div>
                <div v-if="assignedUsers.length" class="divide-y divide-slate-100 rounded-2xl border border-slate-100">
                    <div
                        v-for="user in assignedUsers"
                        :key="user.id"
                        class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ user.name }}
                                <span class="ml-2 text-xs uppercase tracking-wide text-slate-400">{{ user.role }}</span>
                            </p>
                            <p class="text-xs text-slate-500">{{ user.email }}</p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-full border border-slate-200 p-2 text-slate-500 transition hover:border-red-400 hover:text-red-500"
                            @click="openRemovalConfirm(user)"
                        >
                            <span class="sr-only">Remove {{ user.name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500">No users assigned yet.</p>
            </div>
        </div>

        <ConfirmModal
            v-model="showMainConfirm"
            title="Save subdivision changes?"
            confirm-label="Save Changes"
            processing-label="Saving..."
            :loading="form.processing"
            @confirm="submit"
        >
            <p>
                Apply updates to <span class="font-semibold">{{ form.name }}</span> ({{ form.code }}).
            </p>
            <p class="mt-2 text-sm text-gray-600">
                Guard rules and contact info will refresh immediately for all linked gates.
            </p>
        </ConfirmModal>

        <ConfirmModal
            v-model="showAssignConfirm"
            title="Assign this user?"
            confirm-label="Assign User"
            processing-label="Assigning..."
            :loading="assignmentForm.processing"
            @confirm="assignUser"
        >
            <p>
                Add <span class="font-semibold">{{ selectedAssignableUser?.name || 'the selected user' }}</span>
                to <span class="font-semibold">{{ subdivision.name }}</span>.
            </p>
            <p class="mt-2 text-sm text-gray-600">They will gain subdivision-level access immediately.</p>
        </ConfirmModal>

        <ConfirmModal
            v-model="showRemovalConfirm"
            title="Remove this assignment?"
            confirm-label="Remove Access"
            processing-label="Removing..."
            confirm-variant="danger"
            :loading="removalProcessing"
            @confirm="confirmRemoval"
        >
            <p>
                Remove <span class="font-semibold">{{ removalTarget?.name }}</span> from
                <span class="font-semibold">{{ subdivision.name }}</span>? They will lose access to all gates linked to this subdivision.
            </p>
        </ConfirmModal>
    </div>
</template>

<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    subdivision: { type: Object, required: true },
    statusOptions: { type: Array, default: () => [] },
    assignedUsers: { type: Array, default: () => [] },
    availableUsers: { type: Array, default: () => [] },
    metrics: { type: Object, default: () => ({}) },
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
const showMainConfirm = ref(false);
const showAssignConfirm = ref(false);
const showRemovalConfirm = ref(false);
const removalTarget = ref(null);
const removalProcessing = ref(false);

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
        onSuccess: () => {
            showMainConfirm.value = false;
        },
    });
};

const openMainConfirm = () => {
    showMainConfirm.value = true;
};

const assignmentForm = useForm({
    user_id: '',
});

const openAssignConfirm = () => {
    if (!assignmentForm.user_id) return;
    showAssignConfirm.value = true;
};

const assignUser = () => {
    assignmentForm.post(route('subdivisions.users.store', props.subdivision.id), {
        preserveScroll: true,
        onSuccess: () => {
            assignmentForm.reset('user_id');
            showAssignConfirm.value = false;
        },
    });
};

const openRemovalConfirm = (user) => {
    removalTarget.value = user;
    showRemovalConfirm.value = true;
};

const confirmRemoval = () => {
    if (!removalTarget.value) return;
    removalProcessing.value = true;
    router.delete(route('subdivisions.users.destroy', [props.subdivision.id, removalTarget.value.id]), {
        preserveScroll: true,
        onSuccess: () => {
            showRemovalConfirm.value = false;
            removalTarget.value = null;
        },
        onFinish: () => {
            removalProcessing.value = false;
        },
    });
};

const selectedAssignableUser = computed(() =>
    props.availableUsers.find((user) => user.id === assignmentForm.user_id) ?? null
);

const metricCards = computed(() => [
    {
        label: 'Registered gates',
        value: props.metrics?.gates ?? 0,
        hint: 'Linked to this subdivision',
    },
    {
        label: 'Assigned users',
        value: props.metrics?.assigned_users ?? 0,
        hint: 'Admins / employees / guards',
    },
    {
        label: 'Guard roster',
        value: props.metrics?.guards ?? 0,
        hint: 'Active guard assignments',
    },
    {
        label: 'Active passes',
        value: props.metrics?.active_passes ?? 0,
        hint: 'Currently valid',
    },
]);
</script>
