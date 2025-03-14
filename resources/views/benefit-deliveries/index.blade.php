<!-- resources/views/people/index.blade.php -->
<x-app-layout>
    <div class="md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 my-2 ml-2" aria-label="Breadcrumb">
                <ol class="list-reset flex">
                    <li><a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Dashboard</a>
                    </li>
                    <li><span class="mx-2">/</span></li>
                    <li>Benefícios entregues</li>
                </ol>
            </nav>

            <!-- Container Principal -->
            <div class="bg-white md:shadow-md md:rounded-md mf:p-6 px-3 py-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="md:text-xl font-semibold text-gray-800">Benefícios Entregues</h2>
                    <a href="{{ route('benefit-deliveries.create') }}"
                       class="bg-indigo-500 hover:bg-indigo-600 text-center text-white ml-auto px-4 py-2 rounded">Novo
                        registro</a>
                </div>

                <form id="filter-form" class="flex items-start space-x-2 mb-2">
                    <div class="w-full">
                        <input type="text" autocomplete="off" name="filter" id="filter"
                               placeholder="Filtrar por senha, CPF ou nome" class="border rounded px-2 py-1 w-full"/>
                    </div>
                    <button type="submit" class="bg-[orange] text-white px-3 py-1 rounded hover:bg-[#ffb93a]">Filtrar
                    </button>
                </form>

                <div class="overflow-x-auto">
                    <div id="table-container">
                        @include('benefit-deliveries.partials.table', ['benefitDeliveries' => $benefitDeliveries])
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão Flutuante para Entrega Rápida -->
        <button onclick="openQuickDeliveryModal()"
                class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded shadow-lg fixed bottom-5 right-5">
            Entrega Rápida
        </button>
    </div>

    <!-- Modal de Entrega Rápida -->
    <div id="quickDeliveryModal"
         class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg relative w-96">
            <button onclick="closeQuickDeliveryModal()" class="absolute top-2 right-2 text-gray-500 text-2xl">&times;
            </button>
            <h2 class="text-xl font-bold mb-4">Entrega Rápida</h2>
            <input type="text" id="quickDeliveryCode" placeholder="Digite a senha (6 dígitos)"
                   class="border rounded w-full p-2 mb-4" maxlength="6">
            <button type="button" onclick="quickDelivery()"
                    class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Processar
            </button>
        </div>
    </div>

    @push('scripts')
        <script>
            const filterInput = document.getElementById("filter");
            const filterForm = document.getElementById("filter-form");
            const tableContainer = document.getElementById("table-container");
            const quickDeliveryInput = document.getElementById('quickDeliveryCode');
            const urlParams = new URLSearchParams(window.location.search);

            function openModal(benefitDeliveryId) {
                const modalImage = document.getElementById("modalImage");
                const placeholder = "/placeholder.jpg";
                const loadingOverlay = document.getElementById("loading-overlay");

                // Exibe o loading na página
                loadingOverlay.classList.remove("hidden");

                // Define o placeholder antes de carregar a imagem real
                modalImage.src = placeholder;

                fetch(`/benefit-deliveries/${benefitDeliveryId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Pre-carrega a imagem antes de substituir
                        const img = new Image();
                        img.src = data.person.selfie_url;

                        img.onload = function () {
                            modalImage.src = data.person.selfie_url;
                        };

                        img.onerror = function () {
                            modalImage.src = placeholder;
                        };

                        const cpfFormatado = formatCPF(data.person.cpf);
                        const telefoneFormatado = formatPhone(data.person.phone ?? '');

                        document.getElementById("modalTitle").innerText = data.person.name;
                        document.getElementById("modalCpf").innerText = "CPF: " + cpfFormatado;
                        document.getElementById("modalPhone").innerText = "Telefone: " + telefoneFormatado;
                        document.getElementById("modalBenefit").innerText = "Benefício: " + data.benefit.name;
                        document.getElementById("modalTicketCode").innerText = "Senha: " + data.ticket_code;
                        document.getElementById("modalStatus").innerText = "Status: " + data.status;
                        document.getElementById("imageModal").classList.remove("hidden");
                    })
                    .catch(error => {
                        console.error("Erro ao buscar selfie:", error);
                        alert("Erro ao carregar a imagem. Tente novamente.");
                        modalImage.src = placeholder;
                    })
                    .finally(() => {
                        // Esconde o loading quando os dados estiverem carregados
                        loadingOverlay.classList.add("hidden");
                    });
            }

            function formatCPF(cpf) {
                // Remove qualquer caractere que não seja dígito
                cpf = cpf.replace(/\D/g, '');
                // Aplica a máscara 000.000.000-00
                return cpf.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4");
            }

            function formatPhone(phone) {
                // Remove qualquer caractere que não seja dígito
                phone = phone.replace(/\D/g, '');

                // Se tiver 11 dígitos (ex.: 61999999999), formata como (61) 99999-9999
                if (phone.length === 11) {
                    return phone.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
                }
                // Se tiver 10 dígitos (ex.: 6139999999), formata como (61) 3999-9999
                else if (phone.length === 10) {
                    return phone.replace(/^(\d{2})(\d{4})(\d{4})$/, "($1) $2-$3");
                }

                // Caso não atenda ao padrão esperado, retorna o valor sem formatação
                return phone;
            }

            function confirmDelivery(deliveryId) {
                Swal.fire({
                    title: 'Confirmar Entrega?',
                    text: 'Tem certeza de que deseja dar baixa nesta entrega?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, dar baixa!',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/benefit-deliveries/${deliveryId}/deliver`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Entrega Confirmada',
                                        text: data.message,
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        // Atualiza a página ou o DOM dinamicamente
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Erro',
                                        text: data.message,
                                        confirmButtonText: 'OK'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Erro ao dar baixa na entrega:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro Inesperado',
                                    text: 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
                                    confirmButtonText: 'OK'
                                });
                            });
                    }
                });
            }

            function reissueTicket(benefitId) {
                Swal.fire({
                    title: 'Reemitir Ticket?',
                    text: 'Tem certeza de que deseja gerar um novo ticket para esta entrega?',
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sim, reemitir!',
                    confirmButtonColor: '#f59e0b', // Amarelo
                    cancelButtonColor: '#6c757d', // Cinza
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/benefit-deliveries/${benefitId}/reissue`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Ticket Reemitido',
                                        text: data.message,
                                        confirmButtonText: 'OK'
                                    });

                                    // Recarrega apenas a tabela
                                    fetch(window.location.href, {
                                        headers: {'X-Requested-With': 'XMLHttpRequest'}
                                    })
                                        .then(response => response.text())
                                        .then(html => {
                                            document.getElementById("table-container").innerHTML = html;
                                        });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Erro',
                                        text: data.message,
                                        confirmButtonText: 'OK'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Erro ao reemitir ticket:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro Inesperado',
                                    text: 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
                                    confirmButtonText: 'OK'
                                });
                            });
                    }
                });
            }

            function attachDeleteEvents() {
                document.querySelectorAll(".delete-form").forEach(form => {
                    form.addEventListener("submit", function (e) {
                        e.preventDefault(); // Impede o envio padrão do formulário

                        const row = this.closest("tr"); // Obtém a linha da tabela
                        const actionUrl = this.action;

                        Swal.fire({
                            title: 'Confirmar Exclusão?',
                            text: 'Tem certeza de que deseja excluir este registro? Essa ação não pode ser desfeita.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Sim, excluir!',
                            cancelButtonText: 'Cancelar',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch(actionUrl, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    }
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            row.remove();
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Registro Excluído',
                                                text: data.message,
                                                confirmButtonText: 'OK'
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Erro',
                                                text: data.message || 'Não foi possível excluir o registro.',
                                                confirmButtonText: 'OK'
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Erro ao excluir:', error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Erro Inesperado',
                                            text: 'Ocorreu um erro inesperado. Tente novamente.',
                                            confirmButtonText: 'OK'
                                        });
                                    });
                            }
                        });
                    });
                });
            }

            function attachFilterEvents() {
                document.getElementById('filter-form').addEventListener('submit', async function (e) {
                    e.preventDefault();

                    const filter = document.getElementById('filter').value.trim();

                    // 🔥 Bloqueia pesquisas com menos de 3 caracteres, exceto quando limpando
                    if (filter.length > 0 && filter.length < 3) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            text: 'Digite pelo menos 3 caracteres para pesquisar.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    try {
                        const response = await fetch("{{ route('benefit-deliveries.filter') }}?filter=" + encodeURIComponent(filter), {
                            headers: { 'Accept': 'application/json' }
                        });

                        if (!response.ok) {
                            throw new Error("Erro na requisição: " + response.statusText);
                        }

                        const data = await response.json();

                        if (data.success) {
                            document.getElementById('deliveries-table-body').innerHTML = data.html;

                            // 🔥 Remove a paginação quando há um filtro ativo
                            document.getElementById('pagination-links').innerHTML = '';

                            attachDeleteEvents(); // Reanexar eventos de exclusão
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: data.message || 'Falha ao filtrar os registros.'
                            });
                        }
                    } catch (error) {
                        console.error("Erro ao filtrar:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'Não foi possível carregar os registros filtrados. Tente novamente.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }

            function attachPaginationEvents() {
                document.querySelectorAll("#pagination-links a").forEach(link => {
                    link.addEventListener("click", async function (e) {
                        e.preventDefault(); // Impede o comportamento padrão

                        let requestUrl = this.href; // Obtém o link correto da página

                        try {
                            const response = await fetch(requestUrl, { headers: { 'Accept': 'application/json' } });

                            if (!response.ok) {
                                throw new Error(`Erro na requisição: ${response.status} - ${response.statusText}`);
                            }

                            const data = await response.json();

                            if (data.success) {
                                document.getElementById('deliveries-table-body').innerHTML = data.html;
                                document.getElementById('pagination-links').innerHTML = data.pagination; // Usa a paginação custom

                                attachPaginationEvents(); // Reanexar eventos da paginação
                                attachDeleteEvents(); // Reanexar eventos de exclusão
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: data.message || 'Falha ao carregar a nova página.'
                                });
                            }
                        } catch (error) {
                            console.error("Erro ao carregar página:", error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Não foi possível carregar a nova página. Tente novamente.',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                });
            }

            function updateQueryString(key, value) {
                let url = new URL(window.location.href);
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
                return url.toString();
            }

            function openQuickDeliveryModal() {
                document.getElementById('quickDeliveryModal').classList.remove('hidden');
                document.getElementById('quickDeliveryCode').focus();
            }

            function closeQuickDeliveryModal() {
                document.getElementById('quickDeliveryModal').classList.add('hidden');
            }

            async function quickDelivery() {
                const code = document.getElementById('quickDeliveryCode').value.trim();
                if (code.length !== 6) {
                    Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Insira um código de 6 dígitos.', confirmButtonText: 'OK' });
                    return;
                }

                try {
                    const response = await fetch("{{ route('benefit-deliveries.quick-deliver') }}", {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ticket_code: code })
                    });
                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({ icon: 'success', title: 'Baixa Registrada', text: data.message, confirmButtonText: 'OK' })
                            .then(() => {
                                document.getElementById("quickDeliveryCode").value = '';
                                fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                    .then(response => response.text())
                                    .then(html => {
                                        tableContainer.innerHTML = html;
                                        attachPaginationEvents();
                                    });
                            });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Erro', text: data.message || 'Falha ao registrar a baixa.', confirmButtonText: 'OK' });
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    Swal.fire({ icon: 'error', title: 'Erro Inesperado', text: 'Ocorreu um erro inesperado. Tente novamente mais tarde.', confirmButtonText: 'OK' });
                }
            }

            // ** Garante que os eventos são reanexados após cada atualização da tabela **
            document.addEventListener("DOMContentLoaded", function () {
                if (urlParams.has('filter')) {
                    filterInput.value = urlParams.get('filter');
                }

                quickDeliveryInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const code = quickDeliveryInput.value.trim();
                        if (code.length === 6) {
                            quickDelivery(); // Chama a função de baixa rápida
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Atenção',
                                text: 'Insira um código de 6 dígitos.',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                });

                quickDeliveryInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        quickDelivery();
                    }
                });

                attachFilterEvents(); // Chamar a função ao carregar a página
                attachPaginationEvents(); // Chamar a função ao carregar a página
                attachDeleteEvents(); // Chama a função ao carregar a página
            });
        </script>
    @endpush
</x-app-layout>
