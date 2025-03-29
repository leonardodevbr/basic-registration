import Pusher from 'pusher-js';

const currentUserId = document.querySelector('meta[name="user-id"]').content;

Pusher.logToConsole = true;

const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

pusher.subscribe('person-updates').bind('selfie.updated', function (data) {
    const {personId, thumbUrl} = data;

    const row = document.querySelector(`tr[data-person-id="${personId}"]`);
    if (row) {
        const img = row.querySelector("img");
        if (img) {
            // Cria o spinner (tailwind style)
            const spinner = document.createElement('div');
            spinner.className = 'w-16 h-16 rounded-full flex items-center justify-center bg-gray-100';
            spinner.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            `;

            // Substitui a imagem atual pelo spinner temporariamente
            img.replaceWith(spinner);

            // Cria a nova imagem
            const newImg = new Image();
            newImg.src = thumbUrl;
            newImg.className = img.className;
            newImg.style.opacity = 0;

            newImg.onload = () => {
                spinner.replaceWith(newImg); // Troca spinner pela imagem
                setTimeout(() => {
                    newImg.style.transition = 'opacity 0.3s ease';
                    newImg.style.opacity = 1;
                }, 10);
            };
        }
    }
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
