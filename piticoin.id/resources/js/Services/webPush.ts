/**
 * Helper Service untuk WebPush RFC 8292 VAPID di Frontend PitiCoin.id
 */

export function urlBase64ToUint8Array(base64String: string): Uint8Array {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

export function isPushNotificationSupported(): boolean {
    return (
        typeof window !== 'undefined' &&
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        'Notification' in window
    );
}

export function getNotificationPermission(): NotificationPermission | 'unsupported' {
    if (!isPushNotificationSupported()) return 'unsupported';
    return Notification.permission;
}

export async function registerServiceWorker(): Promise<ServiceWorkerRegistration | null> {
    if (!isPushNotificationSupported()) return null;

    try {
        const registration = await navigator.serviceWorker.register('/sw.js', {
            scope: '/',
        });
        await navigator.serviceWorker.ready;
        return registration;
    } catch (error) {
        console.error('Service Worker registration failed:', error);
        return null;
    }
}

export async function getCurrentPushSubscription(): Promise<PushSubscription | null> {
    if (!isPushNotificationSupported()) return null;

    try {
        const registration = await navigator.serviceWorker.ready;
        return await registration.pushManager.getSubscription();
    } catch (error) {
        console.error('Failed to get existing push subscription:', error);
        return null;
    }
}

export async function fetchVapidPublicKey(): Promise<string | null> {
    try {
        const res = await fetch('/api/webpush/vapid-key');
        const data = await res.json();
        return data.publicKey || null;
    } catch (error) {
        console.error('Failed to fetch VAPID public key:', error);
        return null;
    }
}

export async function subscribeUserToPush(): Promise<{ success: boolean; message: string }> {
    if (!isPushNotificationSupported()) {
        return { success: false, message: 'Browser Anda tidak mendukung WebPush notifications.' };
    }

    // 1. Minta izin dari pengguna
    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        return { success: false, message: 'Izin notifikasi ditolak oleh pengguna atau browser.' };
    }

    // 2. Ambil VAPID Public Key dari backend
    const vapidKey = await fetchVapidPublicKey();
    if (!vapidKey) {
        return { success: false, message: 'Kunci VAPID tidak ditemukan di server.' };
    }

    try {
        const registration = await registerServiceWorker();
        if (!registration) {
            return { success: false, message: 'Gagal menginisialisasi Service Worker.' };
        }

        const convertedKey = urlBase64ToUint8Array(vapidKey);
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: convertedKey as unknown as BufferSource,
        });

        // 3. Simpan PushSubscription ke backend
        const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
        const subJson = subscription.toJSON();

        const res = await fetch('/api/webpush/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                keys: subJson.keys,
                contentEncoding: (window as any).PushManager?.supportedContentEncodings?.[0] || 'aes128gcm',
            }),
        });

        const data = await res.json();
        if (res.ok && data.success) {
            return { success: true, message: data.message || 'Notifikasi peramban aktif!' };
        } else {
            return { success: false, message: data.message || 'Gagal menyimpan langganan ke server.' };
        }
    } catch (error: any) {
        console.error('Error subscribing to push:', error);
        return { success: false, message: error.message || 'Terjadi kesalahan saat mengaktifkan notifikasi.' };
    }
}

export async function unsubscribeUserFromPush(): Promise<{ success: boolean; message: string }> {
    if (!isPushNotificationSupported()) {
        return { success: false, message: 'Browser tidak mendukung WebPush.' };
    }

    try {
        const subscription = await getCurrentPushSubscription();
        if (subscription) {
            const endpoint = subscription.endpoint;
            await subscription.unsubscribe();

            const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
            await fetch('/api/webpush/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ endpoint }),
            });
        }

        return { success: true, message: 'Notifikasi berhasil dinonaktifkan.' };
    } catch (error: any) {
        console.error('Error unsubscribing:', error);
        return { success: false, message: error.message || 'Gagal mematikan notifikasi.' };
    }
}

export async function sendTestWebPushNotification(): Promise<{ success: boolean; message: string }> {
    try {
        const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
        const res = await fetch('/api/webpush/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        const data = await res.json();
        return { success: data.success, message: data.message };
    } catch (error: any) {
        return { success: false, message: error.message || 'Gagal mengirim notifikasi uji coba.' };
    }
}
