document.addEventListener("DOMContentLoaded", () => {
    // CPF
    document.querySelectorAll('input[name="person[cpf]"]').forEach(input => {
        IMask(input, {
            mask: '000.000.000-00'
        });
    });

    // Telefone
    document.querySelectorAll('input[name="person[phone]"]').forEach(input => {
        IMask(input, {
            mask: '(00) 00000-0000'
        });
    });

    // Validação rápida antes do submit globalmente
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', (e) => {
            let valid = true;

            form = e.target;

            // validação global simplificada
            form.querySelectorAll('input[required]').forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('border-red-500');
                    input.focus();
                    e.preventDefault();
                } else {
                    input.classList.remove('border-red-500');
                }
            });
        });
    })
});
