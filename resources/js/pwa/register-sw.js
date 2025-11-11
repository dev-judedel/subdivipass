if (typeof window !== 'undefined' && 'serviceWorker' in navigator) {
    const registerServiceWorker = () => {
        const isLocalhost = ['localhost', '127.0.0.1'].includes(window.location.hostname);
        if (window.location.protocol !== 'https:' && !isLocalhost) {
            return;
        }

        navigator.serviceWorker
            .register('/service-worker.js')
            .then((registration) => {
                if (import.meta.env.DEV) {
                    console.info(`[PWA] Service worker registered: ${registration.scope}`);
                }

                // Listen for updates so the UI can prompt the guard to refresh
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (!newWorker) {
                        return;
                    }

                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            window.dispatchEvent(
                                new CustomEvent('sw.updated', {
                                    detail: { registration },
                                })
                            );
                        }
                    });
                });
            })
            .catch((error) => {
                console.error('[PWA] Service worker registration failed', error);
            });
    };

    window.addEventListener('load', registerServiceWorker);
}
