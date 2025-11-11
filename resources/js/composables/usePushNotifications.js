import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const urlBase64ToUint8Array = (base64String) => {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
};

export function usePushNotifications() {
    const isSupported =
        typeof window !== 'undefined' &&
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        'Notification' in window;

    const vapidKey = import.meta.env.VITE_VAPID_PUBLIC_KEY;
    const permission = ref(typeof window !== 'undefined' ? Notification.permission : 'default');
    const isBusy = ref(false);
    const errorMessage = ref('');
    const subscription = ref(null);

    const hasSubscription = computed(() => !!subscription.value);
    const canSubscribe = computed(() => isSupported && vapidKey && permission.value !== 'denied');
    const statusLabel = computed(() => {
        if (!isSupported) {
            return 'Unsupported on this device';
        }
        if (!vapidKey) {
            return 'Missing VAPID key';
        }
        if (permission.value === 'denied') {
            return 'Blocked in browser settings';
        }
        return hasSubscription.value ? 'Enabled' : 'Disabled';
    });

    const resolveRegistration = async () => {
        if (!isSupported) {
            return null;
        }
        const registration = await navigator.serviceWorker.ready;
        return registration;
    };

    const refreshSubscription = async () => {
        try {
            const registration = await resolveRegistration();
            if (!registration) {
                subscription.value = null;
                return;
            }

            subscription.value = await registration.pushManager.getSubscription();
        } catch (error) {
            console.warn('[PWA] Unable to read push subscription', error);
            subscription.value = null;
        }
    };

    const subscribe = async () => {
        if (!canSubscribe.value) {
            return;
        }

        isBusy.value = true;
        errorMessage.value = '';

        try {
            if (permission.value !== 'granted') {
                permission.value = await Notification.requestPermission();
            }

            if (permission.value !== 'granted') {
                errorMessage.value = 'Notifications require browser permission.';
                return;
            }

            const registration = await resolveRegistration();
            if (!registration) {
                errorMessage.value = 'Service worker not ready yet.';
                return;
            }

            const newSubscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidKey),
            });

            await axios.post(route('guard.push-subscriptions.store'), newSubscription.toJSON());
            subscription.value = newSubscription;
        } catch (error) {
            console.error('[PWA] Unable to enable push notifications', error);
            errorMessage.value = error.response?.data?.message ?? 'Failed to enable notifications.';
        } finally {
            isBusy.value = false;
        }
    };

    const unsubscribe = async () => {
        if (!subscription.value) {
            return;
        }

        isBusy.value = true;
        errorMessage.value = '';

        try {
            await axios.delete(route('guard.push-subscriptions.destroy'), {
                data: { endpoint: subscription.value.endpoint },
            });
            await subscription.value.unsubscribe();
            subscription.value = null;
        } catch (error) {
            console.error('[PWA] Unable to disable push notifications', error);
            errorMessage.value = error.response?.data?.message ?? 'Failed to disable notifications.';
        } finally {
            isBusy.value = false;
        }
    };

    const handleSwMessage = (event) => {
        if (event.data?.type === 'PUSH_SUBSCRIPTION_CHANGED') {
            refreshSubscription();
        }
    };

    onMounted(() => {
        refreshSubscription();
        if (isSupported) {
            navigator.serviceWorker.addEventListener('message', handleSwMessage);
        }
    });

    onBeforeUnmount(() => {
        if (isSupported) {
            navigator.serviceWorker.removeEventListener('message', handleSwMessage);
        }
    });

    return {
        isSupported,
        permission,
        statusLabel,
        isBusy,
        errorMessage,
        hasSubscription,
        canSubscribe,
        subscribe,
        unsubscribe,
    };
}
