self.addEventListener('push', function (event) {
    const data = event.data?.json();

    const options = {
        body: data.body || 'Você tem uma nova notificação.',
        icon: data.icon || '/favicon.ico',
        badge: '/favicon.ico',
        data: {
            url: data.data?.url || '/',
        }
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Abre a URL correspondente quando o usuário clica na notificação
self.addEventListener("notificationclick", function (event) {
    event.notification.close();

    const url = event.notification.data?.url;
    if (!url) return;

    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true }).then(clientList => {
            for (let client of clientList) {
                if (client.url === url && "focus" in client) {
                    return client.focus();
                }
            }
            return clients.openWindow(url);
        })
    );
});
