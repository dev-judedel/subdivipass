<template>
    <transition name="fade">
        <div
            v-if="modelValue"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0"
        >
            <div
                class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
                @click="handleBackdrop"
            ></div>

            <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5">
                <div class="flex items-start gap-4">
                    <div class="mt-1 shrink-0">
                        <slot name="icon">
                            <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </slot>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            {{ title }}
                        </h3>
                        <p v-if="message" class="mt-2 text-sm text-gray-600">
                            {{ message }}
                        </p>
                        <div class="mt-3 text-sm text-gray-700 leading-relaxed">
                            <slot />
                        </div>
                    </div>
                    <button
                        class="text-gray-400 hover:text-gray-600 transition"
                        @click="handleCancel"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:items-center gap-3 pt-3">
                    <button
                        v-if="!hideCancel"
                        type="button"
                        class="w-full sm:w-auto px-5 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition disabled:opacity-50"
                        :disabled="loading"
                        @click="handleCancel"
                    >
                        {{ cancelLabel }}
                    </button>
                    <button
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 font-semibold rounded-lg shadow-md transition text-white"
                        :class="confirmButtonClass"
                        :disabled="loading"
                        @click="handleConfirm"
                    >
                        <svg
                            v-if="loading"
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4l3-3-3-3v4a10 10 0 00-10 10h4z"
                            ></path>
                        </svg>
                        {{ loading ? processingLabel : confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirm Action',
    },
    message: {
        type: String,
        default: '',
    },
    confirmLabel: {
        type: String,
        default: 'Confirm',
    },
    processingLabel: {
        type: String,
        default: 'Processing...',
    },
    cancelLabel: {
        type: String,
        default: 'Cancel',
    },
    confirmVariant: {
        type: String,
        default: 'primary', // primary | danger | success
    },
    loading: {
        type: Boolean,
        default: false,
    },
    hideCancel: {
        type: Boolean,
        default: false,
    },
    closeOnBackdrop: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);

const confirmButtonClass = computed(() => {
    if (props.confirmVariant === 'danger') {
        return 'bg-red-600 hover:bg-red-700 disabled:bg-red-400';
    }
    if (props.confirmVariant === 'success') {
        return 'bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400';
    }
    return 'bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400';
});

const close = () => {
    emit('update:modelValue', false);
};

const handleCancel = () => {
    emit('cancel');
    close();
};

const handleConfirm = () => {
    emit('confirm');
};

const handleBackdrop = () => {
    if (!props.closeOnBackdrop) return;
    handleCancel();
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
