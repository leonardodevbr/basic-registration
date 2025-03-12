import './bootstrap';
import './form-validation.js';
import './selfieCapture.js';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            Swal.fire({
                title: 'Tem certeza?',
                text: "Essa ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
