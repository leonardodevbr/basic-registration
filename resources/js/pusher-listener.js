import Pusher from 'pusher-js';

const currentUserId = document.querySelector('meta[name="user-id"]').content;

Pusher.logToConsole = true;

const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

pusher.subscribe('benefit-status').bind('status.updated', function (data) {
    if (parseInt(data.updated_by) === parseInt(currentUserId)) return;

    const { personId, status } = data;
    const statusLabel = {
        'PENDING': 'Pendente',
        'DELIVERED': 'Entregue',
        'EXPIRED': 'Expirada',
        'REISSUED': 'Reemitida'
    }[status] || 'Atualizada';

    // Toast visual
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-white border-l-4 border-blue-500 shadow-lg p-4 rounded-md z-50 animate-fade-in';
    toast.innerHTML = `
        <strong class="text-blue-600 block mb-1">Entrega Atualizada</strong>
        <span>Entrega de pessoa ${personId} marcada como <b>${statusLabel}</b>.</span>
    `;
    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 5000); // Remove depois de 5s

    // Incrementa badge (sino)
    let badge = document.querySelector('#notification-badge');
    if (badge) {
        badge.textContent = parseInt(badge.textContent || "0") + 1;
        badge.classList.remove('hidden');
    }

    // Salva no localStorage (pra exibir depois numa lista)
    const notifications = JSON.parse(localStorage.getItem('notifications') || '[]');
    notifications.unshift({
        message: `Entrega de pessoa ${personId} marcada como ${statusLabel}.`,
        timestamp: new Date().toISOString()
    });
    localStorage.setItem('notifications', JSON.stringify(notifications));
});


pusher.subscribe('benefit-status').bind('status.updated', function (data) {
    if (parseInt(data.updated_by) === parseInt(currentUserId)) return;

    // Aqui você pode emitir uma notificação visual
    console.log(`Status atualizado da entrega (pessoa ${data.personId}): ${data.status}`);

    const row = document.querySelector(`tr[data-person-id="${data.personId}"]`);
    if (row) {
        row.classList.add('bg-yellow-50');
        setTimeout(() => row.classList.remove('bg-yellow-50'), 2000);
        row.remove();
    }
});
