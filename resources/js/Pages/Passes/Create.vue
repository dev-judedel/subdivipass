<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Create New Pass</h1>
                        <p class="mt-1 text-sm text-gray-500">Fill in the details to create a new pass</p>
                    </div>
                    <Link
                        :href="route('passes.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to List
                    </Link>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <form @submit.prevent="openPreview" class="space-y-6">
                <!-- Subdivision and Pass Type Selection -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Pass Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Subdivision -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Subdivision <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.subdivision_id"
                                @change="onSubdivisionChange"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.subdivision_id }"
                            >
                                <option value="">Select Subdivision</option>
                                <option
                                    v-for="subdivision in subdivisions"
                                    :key="subdivision.id"
                                    :value="subdivision.id"
                                >
                                    {{ subdivision.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.subdivision_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.subdivision_id }}
                            </p>
                        </div>

                        <!-- Pass Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pass Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.pass_type_id"
                                @change="onPassTypeChange"
                                :disabled="!form.subdivision_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100"
                                :class="{ 'border-red-500': form.errors.pass_type_id }"
                            >
                                <option value="">Select Pass Type</option>
                                <option
                                    v-for="type in filteredPassTypes"
                                    :key="type.id"
                                    :value="type.id"
                                >
                                    {{ type.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.pass_type_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.pass_type_id }}
                            </p>
                            <p v-if="selectedPassType" class="mt-1 text-xs text-gray-500">
                                {{ selectedPassType.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Visitor Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Visitor Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Visitor Name -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Visitor Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.visitor_name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.visitor_name }"
                                placeholder="Enter visitor's full name"
                            />
                            <p v-if="form.errors.visitor_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.visitor_name }}
                            </p>
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Contact Number
                            </label>
                            <input
                                v-model="form.visitor_contact"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.visitor_contact }"
                                placeholder="+63 XXX XXX XXXX"
                            />
                            <p v-if="form.errors.visitor_contact" class="mt-1 text-sm text-red-600">
                                {{ form.errors.visitor_contact }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input
                                v-model="form.visitor_email"
                                type="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.visitor_email }"
                                placeholder="visitor@example.com"
                            />
                            <p v-if="form.errors.visitor_email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.visitor_email }}
                            </p>
                        </div>

                        <!-- Company -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Company Name
                            </label>
                            <input
                                v-model="form.visitor_company"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.visitor_company }"
                                placeholder="Enter company name (if applicable)"
                            />
                            <p v-if="form.errors.visitor_company" class="mt-1 text-sm text-red-600">
                                {{ form.errors.visitor_company }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Information (Optional) -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Vehicle Information (Optional)</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Vehicle Plate -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Plate Number
                            </label>
                            <input
                                v-model="form.vehicle_plate"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.vehicle_plate }"
                                placeholder="ABC 1234"
                            />
                            <p v-if="form.errors.vehicle_plate" class="mt-1 text-sm text-red-600">
                                {{ form.errors.vehicle_plate }}
                            </p>
                        </div>

                        <!-- Vehicle Model -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Vehicle Model
                            </label>
                            <input
                                v-model="form.vehicle_model"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.vehicle_model }"
                                placeholder="Toyota Vios 2020"
                            />
                            <p v-if="form.errors.vehicle_model" class="mt-1 text-sm text-red-600">
                                {{ form.errors.vehicle_model }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Visit Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Visit Details</h2>

                    <div class="space-y-6">
                        <!-- Purpose -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Purpose of Visit <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="form.purpose"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.purpose }"
                                placeholder="Please describe the purpose of this visit"
                            ></textarea>
                            <p v-if="form.errors.purpose" class="mt-1 text-sm text-red-600">
                                {{ form.errors.purpose }}
                            </p>
                        </div>

                        <!-- Destination -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Destination Address
                            </label>
                            <input
                                v-model="form.destination"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.destination }"
                                placeholder="Block & Lot, Street name"
                            />
                            <p v-if="form.errors.destination" class="mt-1 text-sm text-red-600">
                                {{ form.errors.destination }}
                            </p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Notes
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.notes }"
                                placeholder="Any additional information or special instructions"
                            ></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
                                {{ form.errors.notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Validity Period -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Validity Period</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Valid From -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Valid From
                            </label>
                            <input
                                v-model="form.valid_from"
                                type="datetime-local"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.valid_from }"
                            />
                            <p v-if="form.errors.valid_from" class="mt-1 text-sm text-red-600">
                                {{ form.errors.valid_from }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Leave empty to use current date/time
                            </p>
                        </div>

                        <!-- Valid To -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Valid To
                            </label>
                            <input
                                v-model="form.valid_to"
                                type="datetime-local"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.valid_to }"
                            />
                            <p v-if="form.errors.valid_to" class="mt-1 text-sm text-red-600">
                                {{ form.errors.valid_to }}
                            </p>
                            <p v-if="selectedPassType" class="mt-1 text-xs text-gray-500">
                                Default duration: {{ selectedPassType.default_validity_hours }} hours
                            </p>
                        </div>
                    </div>

                    <!-- Info about approval -->
                    <div v-if="selectedPassType?.requires_approval" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-800">
                                    This pass type requires approval from an administrator before it becomes active.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div v-if="showPreview" class="bg-white rounded-lg shadow-sm border border-blue-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm uppercase text-blue-500 font-semibold tracking-wide">Review Details</p>
                            <h3 class="text-xl font-semibold text-gray-900">Pass Summary</h3>
                        </div>
                        <span
                            v-if="selectedPassType?.requires_approval"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800"
                        >
                            Approval Required
                        </span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Subdivision</p>
                                <p class="text-base font-semibold text-gray-900">{{ selectedSubdivision?.name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Pass Type</p>
                                <p class="text-base font-semibold text-gray-900">{{ selectedPassType?.name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500" v-if="selectedPassType?.description">{{ selectedPassType.description }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Validity Window</p>
                                <p class="text-sm text-gray-900">{{ formatDateTime(form.valid_from) }} - {{ formatDateTime(form.valid_to) }}</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Visitor</p>
                                <p class="text-base font-semibold text-gray-900">{{ form.visitor_name || 'N/A' }}</p>
                                <p class="text-sm text-gray-500" v-if="form.visitor_contact">{{ form.visitor_contact }}</p>
                                <p class="text-sm text-gray-500" v-if="form.visitor_email">{{ form.visitor_email }}</p>
                                <p class="text-sm text-gray-500" v-if="form.visitor_company">{{ form.visitor_company }}</p>
                            </div>
                            <div v-if="form.vehicle_plate || form.vehicle_model">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Vehicle</p>
                                <p class="text-sm text-gray-900">
                                    {{ form.vehicle_plate || 'N/A' }}
                                    <span class="text-gray-500" v-if="form.vehicle_model">({{ form.vehicle_model }})</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Destination & Purpose</p>
                                <p class="text-sm text-gray-900">{{ form.destination || 'N/A' }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ form.purpose || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="px-4 py-2 text-gray-700 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition"
                            @click="showPreview = false"
                        >
                            Back to Edit
                        </button>
                        <button
                            type="button"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition"
                            @click="openConfirmation"
                        >
                            Continue to Confirmation
                        </button>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4">
                    <Link
                        :href="route('passes.index')"
                        class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition duration-150"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                    >
                        Review Details
                    </button>
                </div>

                <!-- General Error -->
                <div v-if="form.errors.error" class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-red-800">{{ form.errors.error }}</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <ConfirmModal
            v-model="showConfirm"
            title="Create this pass?"
            confirm-label="Confirm & Create Pass"
            processing-label="Creating Pass..."
            :loading="form.processing"
            @confirm="confirmSubmit"
            @cancel="showConfirm = false"
        >
            <p>
                You're about to create a <span class="font-semibold">{{ selectedPassType?.name ?? 'pass' }}</span>
                for <span class="font-semibold">{{ form.visitor_name || 'an unnamed visitor' }}</span>
                in <span class="font-semibold">{{ selectedSubdivision?.name ?? 'N/A' }}</span>. Make sure all details look correct before proceeding.
            </p>

            <div class="mt-4 bg-gray-50 rounded-lg p-4 text-sm text-gray-600 space-y-2">
                <p>
                    <span class="font-semibold text-gray-800">Validity:</span>
                    {{ formatDateTime(form.valid_from) }} - {{ formatDateTime(form.valid_to) }}
                </p>
                <p v-if="form.destination">
                    <span class="font-semibold text-gray-800">Destination:</span>
                    {{ form.destination }}
                </p>
                <p v-if="form.notes">
                    <span class="font-semibold text-gray-800">Notes:</span>
                    {{ form.notes }}
                </p>
            </div>
        </ConfirmModal>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

defineOptions({ layout: DashboardLayout });

// Props
const props = defineProps({
    subdivisions: Array,
    passTypes: Array,
});

// Form
const form = useForm({
    subdivision_id: '',
    pass_type_id: '',
    visitor_name: '',
    visitor_contact: '',
    visitor_email: '',
    visitor_company: '',
    vehicle_plate: '',
    vehicle_model: '',
    purpose: '',
    destination: '',
    notes: '',
    valid_from: '',
    valid_to: '',
});

// UI state
const showPreview = ref(false);
const showConfirm = ref(false);

// Computed
const filteredPassTypes = computed(() => {
    if (!form.subdivision_id) return [];
    return props.passTypes.filter(type => type.subdivision_id == form.subdivision_id);
});

const selectedSubdivision = computed(() => {
    if (!form.subdivision_id) return null;
    return props.subdivisions.find(subdivision => subdivision.id == form.subdivision_id) ?? null;
});

const selectedPassType = computed(() => {
    if (!form.pass_type_id) return null;
    return props.passTypes.find(type => type.id == form.pass_type_id);
});

const formatDateTime = (value) => {
    if (!value) return 'Not set';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
};

// Methods
const resetReviewState = () => {
    showPreview.value = false;
    showConfirm.value = false;
};

const onSubdivisionChange = () => {
    // Reset pass type when subdivision changes
    form.pass_type_id = '';
    resetReviewState();
};

const onPassTypeChange = () => {
    // Could auto-populate validity dates based on pass type defaults here
    // For now, we let the backend handle the default validity calculation
    resetReviewState();
};

const openPreview = () => {
    showPreview.value = true;
    showConfirm.value = false;
};

const openConfirmation = () => {
    showConfirm.value = true;
};

const confirmSubmit = () => {
    form.post(route('passes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showPreview.value = false;
            showConfirm.value = false;
        },
    });
};
</script>
