<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold text-gray-900">Edit Pass</h1>
                            <span :class="getStatusBadgeClass(pass.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
                                {{ pass.status?.toUpperCase() }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ pass.pass_number }}</p>
                    </div>
                    <Link
                        :href="route('passes.show', pass.id)"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Details
                    </Link>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Info Banner -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-blue-800">
                            Note: Subdivision and Pass Type cannot be changed after creation. If validity dates are modified, a new QR code will be generated.
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Pass Information (Read-only) -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Pass Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Subdivision (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Subdivision
                            </label>
                            <input
                                type="text"
                                :value="pass.subdivision?.name"
                                disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed"
                            />
                            <p class="mt-1 text-xs text-gray-500">Cannot be changed</p>
                        </div>

                        <!-- Pass Type (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pass Type
                            </label>
                            <input
                                type="text"
                                :value="pass.type?.name"
                                disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed"
                            />
                            <p class="mt-1 text-xs text-gray-500">Cannot be changed</p>
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
                        </div>
                    </div>

                    <!-- QR Regeneration Notice -->
                    <div v-if="validityChanged" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-800">
                                    The validity period has been modified. A new QR code will be generated when you save these changes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4">
                    <Link
                        :href="route('passes.show', pass.id)"
                        class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition duration-150"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Saving Changes...</span>
                        <span v-else>Save Changes</span>
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
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';

// Props
const props = defineProps({
    pass: Object,
    subdivisions: Array,
    passTypes: Array,
});

// Form - pre-populate with existing data
const form = useForm({
    visitor_name: props.pass.visitor_name || '',
    visitor_contact: props.pass.visitor_contact || '',
    visitor_email: props.pass.visitor_email || '',
    visitor_company: props.pass.visitor_company || '',
    vehicle_plate: props.pass.vehicle_plate || '',
    vehicle_model: props.pass.vehicle_model || '',
    purpose: props.pass.purpose || '',
    destination: props.pass.destination || '',
    notes: props.pass.notes || '',
    valid_from: props.pass.valid_from ? formatDateTimeLocal(props.pass.valid_from) : '',
    valid_to: props.pass.valid_to ? formatDateTimeLocal(props.pass.valid_to) : '',
});

// Computed
const validityChanged = computed(() => {
    const originalFrom = props.pass.valid_from ? formatDateTimeLocal(props.pass.valid_from) : '';
    const originalTo = props.pass.valid_to ? formatDateTimeLocal(props.pass.valid_to) : '';
    return form.valid_from !== originalFrom || form.valid_to !== originalTo;
});

// Methods
const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        active: 'bg-green-100 text-green-800',
        expired: 'bg-red-100 text-red-800',
        revoked: 'bg-orange-100 text-orange-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDateTimeLocal = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    // Format for datetime-local input: YYYY-MM-DDTHH:mm
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const submit = () => {
    form.put(route('passes.update', props.pass.id), {
        onSuccess: () => {
            // Will redirect to pass show page
        },
    });
};
</script>
