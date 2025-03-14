<!-- resources/views/people/index.blade.php -->
<x-app-layout>
    <div class="md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 my-2 ml-2" aria-label="Breadcrumb">
                <ol class="list-reset flex">
                    <li><a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Dashboard</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li>Benefícios entregues</li>
                </ol>
            </nav>

            <div class="mb-4">
                <form id="filter-form" class="flex items-start space-x-2">
                    <div class="w-full">
                        <input type="text" autocomplete="off" name="filter" id="filter" placeholder="Filtrar por senha, CPF ou nome" class="border rounded px-2 py-1 w-full" />
                        <div id="filter-error" class="text-red-500 text-sm mt-1"></div>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                        Filtrar
                    </button>
                </form>
            </div>

            <div class="bg-white md:shadow-md md:rounded-md mf:p-6 px-3 py-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="md:text-xl font-semibold text-gray-800">Benefícios Entregues</h2>
                    <button onclick="openQuickDeliveryModal()" class="bg-[orange] text-white ml-auto mr-2 px-4 py-2 rounded hover:bg-blue-600">
                        Baixa Rápida
                    </button>
                    <a href="{{ route('benefit-deliveries.create') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md">Novo registro</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto md:table-fixed divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left">Selfie</th>
                            <th class="py-3 px-6 text-left">Nome</th>
                            <th class="py-3 px-6 text-left">CPF</th>
                            <th class="py-3 px-6 text-left">Telefone</th>
                            <th class="py-3 px-6 text-left">Benefício</th>
                            <th class="py-3 px-6 text-left">Data</th>
                            <th class="py-3 px-6 text-left">Ações</th>
                        </tr>
                        </thead>
                        <tbody id="deliveries-table-body">
                        @foreach($benefitDeliveries as $benefitDelivery)
                            <tr class="border-b hover:bg-gray-50" data-code="{{ $benefitDelivery->password_code }}">
                                <td class="py-4 md:px-6 cursor-pointer">
                                    @if($benefitDelivery->person->selfie_path)
                                        <img src="{{ $benefitDelivery->person->thumb_url }}"
                                             alt="Selfie"
                                             loading="lazy"
                                             class="w-16 h-16 rounded-full object-cover mx-auto"
                                             onclick="openModal('{{ $benefitDelivery->id }}')">
                                    @else
                                        <span class="text-gray-500">Sem selfie</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap max-w-[150px] truncate">
                                    {{ $benefitDelivery->person->name }}
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    {{ preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $benefitDelivery->person->cpf) }}
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    {{ preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", $benefitDelivery->person->phone) }}
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap max-w-[150px] truncate">
                                    <span>{{ $benefitDelivery->benefit->name }}</span><br>
                                    <div class="status-col">
                                        @switch($benefitDelivery->status)
                                            @case('PENDING')
                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Pendente</span>
                                                @break
                                            @case('DELIVERED')
                                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Entregue</span>
                                                @break
                                            @case('EXPIRED')
                                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Expirado</span>
                                                @break
                                        @endswitch
                                    </div>
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap max-w-[150px] truncate">
                                    {{ $benefitDelivery->benefit->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('benefit-deliveries.edit', $benefitDelivery) }}"
                                           class="bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600">
                                            Editar
                                        </a>
                                        <form action="{{ route('benefit-deliveries.destroy', $benefitDelivery) }}"
                                              method="POST" class="inline-block delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                                                Excluir
                                            </button>
                                        </form>
                                        @if($benefitDelivery->status === 'PENDING')
                                            <button type="button"
                                                    class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600"
                                                    onclick="confirmDelivery({{ $benefitDelivery->id }})">
                                                Dar Baixa
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $benefitDeliveries->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="imageModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg relative">
            <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500">&times;</button>
            <h2 id="modalTitle" class="text-xl font-bold"></h2>
            <p id="modalCpf" class="text-sm text-gray-500"></p>
            <p id="modalPhone" class="text-sm text-gray-500"></p>
            <p id="modalBenefit" class="text-sm text-gray-500"></p>
            <p id="modalPasswordCode" class="text-sm text-gray-500"></p>
            <p id="modalStatus" class="text-sm text-gray-500 mb-4"></p>
            <img id="modalImage" src="" class="w-80 h-80 rounded">
        </div>
    </div>

    <!-- Quick Delivery Modal -->
    <div id="quickDeliveryModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg relative w-96">
            <button onclick="closeQuickDeliveryModal()" class="absolute top-2 right-2 text-gray-500 text-2xl">&times;</button>
            <h2 class="text-xl font-bold mb-4">Baixa Rápida</h2>
            <input type="text" id="quickDeliveryCode" placeholder="Digite a senha (6 dígitos)" class="border rounded w-full p-2 mb-4" maxlength="6">
            <button type="button" onclick="quickDelivery()" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Dar Baixa Rápida
            </button>
        </div>
    </div>

@push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const filterInput = document.getElementById("filter");
                const filterError = document.getElementById("filter-error");
                const loadingOverlay = document.getElementById("loading-overlay");

                // Flag para resetar o filtro quando estiver vazio
                let emptyFilterSent = false;
                // Variável para armazenar o último filtro enviado (com pelo menos 3 caracteres)
                let lastSubmittedFilter = "";

                filterInput?.addEventListener("input", function () {
                    let value = filterInput.value;

                    // Se o valor contiver apenas dígitos, aplica a máscara do CPF se tiver mais de 6 dígitos
                    if (/^\d/.test(value)) {
                        value = value.replace(/\D/g, ""); // Remove tudo que não for número

                        if (value.length > 6) value = value.replace(/^(\d{3})(\d)/, "$1.$2");
                        if (value.length > 6) value = value.replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3");
                        if (value.length > 9) value = value.replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, "$1.$2.$3-$4");

                        if (value.length > 14) {
                            value = value.slice(0, 14);
                        }
                    }

                    filterInput.value = value; // Atualiza o campo

                    // Se o campo for limpo, reseta lastSubmittedFilter e dispara o reset apenas uma vez
                    if (value.trim() === '') {
                        lastSubmittedFilter = "";
                        if (!emptyFilterSent) {
                            emptyFilterSent = true;
                            filterError.classList.add("hidden");
                            loadingOverlay.classList.remove("hidden");
                            fetch("{{ route('benefit-deliveries.filter') }}?filter=", {
                                headers: { 'Accept': 'application/json' }
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        document.getElementById("deliveries-table-body").innerHTML = data.html;
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Erro',
                                            text: data.message || 'Falha ao carregar os registros.',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error("Erro ao resetar filtro:", error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Erro',
                                        text: 'Ocorreu um erro ao resetar o filtro. Tente novamente.',
                                        confirmButtonText: 'OK'
                                    });
                                })
                                .finally(() => {
                                    loadingOverlay.classList.add("hidden");
                                });
                        }
                    } else {
                        // Se houver conteúdo, reseta a flag de reset e o último filtro enviado
                        emptyFilterSent = false;
                        lastSubmittedFilter = "";
                    }
                });

                filterInput?.addEventListener("keypress", async function (e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        const filtro = filterInput.value.trim();

                        // Se estiver vazio, já foi tratado no "input", então apenas retorna
                        if (filtro === "") {
                            return;
                        }

                        // Se o filtro tiver menos de 3 caracteres, exibe mensagem de erro e não envia a requisição
                        if (filtro.length < 3) {
                            filterError.textContent = "Digite pelo menos 3 caracteres para a busca.";
                            filterError.classList.remove("hidden");
                            return;
                        } else {
                            filterError.classList.add("hidden");
                        }

                        // Verifica se o mesmo filtro já foi enviado
                        if (filtro === lastSubmittedFilter) {
                            return;
                        }
                        lastSubmittedFilter = filtro;

                        loadingOverlay.classList.remove("hidden");

                        try {
                            const response = await fetch("{{ route('benefit-deliveries.filter') }}?filter=" + encodeURIComponent(filtro), {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await response.json();
                            if (data.success) {
                                document.getElementById("deliveries-table-body").innerHTML = data.html;
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: data.message || 'Falha ao filtrar os registros.',
                                    confirmButtonText: 'OK'
                                });
                            }
                        } catch (error) {
                            console.error("Erro ao filtrar:", error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao filtrar os registros. Tente novamente.',
                                confirmButtonText: 'OK'
                            });
                        } finally {
                            loadingOverlay.classList.add("hidden");
                        }
                    }
                });
            });


            // Função para abrir o modal e focar no input
            function openQuickDeliveryModal() {
                const modal = document.getElementById('quickDeliveryModal');
                modal.classList.remove('hidden');
                document.getElementById('quickDeliveryCode').focus();
            }

            // Função para fechar o modal
            function closeQuickDeliveryModal() {
                document.getElementById('quickDeliveryModal').classList.add('hidden');
            }

            // Listener para enviar ao pressionar ENTER, somente se houver 6 dígitos
            const quickDeliveryInput = document.getElementById('quickDeliveryCode');
            quickDeliveryInput.addEventListener('keypress', function(e) {
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

            // Função para processar a baixa rápida
            async function quickDelivery() {
                const code = quickDeliveryInput.value.trim();
                if (code.length !== 6) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'Insira um código de 6 dígitos.',
                        confirmButtonText: 'OK'
                    });
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
                        body: JSON.stringify({ password_code: code })
                    });
                    const data = await response.json();
                    if (response.ok && data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Baixa Registrada',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Limpa o campo e foca novamente para nova entrada
                            quickDeliveryInput.value = '';
                            quickDeliveryInput.focus();

                            // Atualiza a linha na tabela: busca pelo atributo data-code
                            const row = document.querySelector(`[data-code="${code}"]`);
                            if (row) {
                                // Atualiza o status para "Entregue"
                                const statusCell = row.querySelector('.status-col');
                                if (statusCell) {
                                    statusCell.innerHTML = `<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Entregue</span>`;
                                }
                                // Oculta o botão "Dar Baixa" na linha
                                const deliverButton = row.querySelector('button[onclick^="confirmDelivery"]');
                                if (deliverButton) {
                                    deliverButton.style.display = 'none';
                                }
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.message || 'Falha ao registrar a baixa.',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro Inesperado',
                        text: 'Ocorreu um erro inesperado. Tente novamente.',
                        confirmButtonText: 'OK'
                    });
                }
            }

            document.getElementById('filter-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const filter = document.getElementById('filter').value.trim();
                try {
                    const response = await fetch("{{ route('benefit-deliveries.filter') }}?filter=" + encodeURIComponent(filter), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        // Atualize o corpo da tabela com o HTML retornado
                        document.getElementById('deliveries-table-body').innerHTML = data.html;
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
                        text: 'Ocorreu um erro ao filtrar os registros. Tente novamente.'
                    });
                }
            });

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
                        document.getElementById("modalPasswordCode").innerText = "Senha: " + data.password_code;
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


            function closeModal() {
                document.getElementById("imageModal").classList.add("hidden");
            }


            function closeModal() {
                document.getElementById("imageModal").classList.add("hidden");
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
                    cancelButtonText: 'Cancelar'
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

        </script>
    @endpush
</x-app-layout>
