import Pusher from 'pusher-js';

Pusher.logToConsole = false;

const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

const channel = pusher.subscribe('person-updates');

channel.bind('selfie.updated', function (data) {
    const { personId, thumbUrl } = data;

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
