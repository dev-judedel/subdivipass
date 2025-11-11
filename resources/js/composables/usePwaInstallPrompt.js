import { onBeforeUnmount, onMounted, ref } from 'vue';

export function usePwaInstallPrompt() {
    const canInstall = ref(false);
    const deferredPrompt = ref(null);

    const isStandalone = () => {
        if (typeof window === 'undefined') {
            return false;
        }

        return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    };

    const beforeInstallHandler = (event) => {
        event.preventDefault();
        deferredPrompt.value = event;
        canInstall.value = true;
    };

    const promptInstall = async () => {
        if (!deferredPrompt.value) {
            return;
        }

        deferredPrompt.value.prompt();
        try {
            await deferredPrompt.value.userChoice;
        } finally {
            deferredPrompt.value = null;
            canInstall.value = false;
        }
    };

    const dismissInstall = () => {
        canInstall.value = false;
        deferredPrompt.value = null;
    };

    onMounted(() => {
        if (isStandalone()) {
            return;
        }

        window.addEventListener('beforeinstallprompt', beforeInstallHandler);
    });

    onBeforeUnmount(() => {
        window.removeEventListener('beforeinstallprompt', beforeInstallHandler);
    });

    return {
        canInstall,
        promptInstall,
        dismissInstall,
    };
}
