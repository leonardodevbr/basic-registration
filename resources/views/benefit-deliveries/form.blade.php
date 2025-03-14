@if($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="w-full mx-auto bg-white rounded-lg pb-6">
    <!-- Error container for AJAX errors -->
    <div id="ajax-error" class="mb-4 p-4 bg-red-100 text-red-700 rounded hidden"></div>

    <form id="benefit-delivery-register-form" action="{{ $action }}" class="grid grid-cols-1 md:grid-cols-2 gap-4"
          method="POST">
        @csrf
        @method($method)
        <!-- (Rest of the form remains unchanged) -->
        <div class="flex flex-col">
            <div class="mb-4">
                <label class="block">Selfie:</label>
                @php
                    $selfieValue = old('person.selfie', $benefitDelivery->person->selfie_url ?? null);
                @endphp

                <div id="error-message" class="text-red-500 mt-2 hidden"></div>

                <div class="video-container {{ $selfieValue ? 'hidden' : '' }}">
                    <video id="video" class="border rounded w-full" autoplay></video>
                    <canvas id="canvas"></canvas>
                </div>
                <img id="selfie-preview" class="border rounded w-full mt-2 {{ $selfieValue ? '' : 'hidden' }}"
                     src="{{ $selfieValue }}" alt="Selfie">

                <input type="hidden" name="person[selfie]" id="selfie" value="{{ $selfieValue }}">

                <div class="flex mt-2">
                    <button type="button" id="flip-btn" class="px-4 mr-2 py-2 bg-gray-500 text-white rounded w-full {{ $selfieValue ? 'hidden' : '' }}">
                        Espelhar
                    </button>
                    <button type="button" id="capture-btn" class="px-4 mr-2 py-2 bg-blue-500 text-white rounded w-full {{ $selfieValue ? 'hidden' : '' }}">
                        Tirar Selfie
                    </button>
                    <button type="button" id="cancel-btn"
                            class="px-4 py-2 bg-red-500 text-white rounded w-full {{ $selfieValue ? '' : 'hidden' }}">
                        Cancelar
                    </button>
                    <button type="button" id="switch-camera-btn" class="bg-gray-200/80 px-4 py-2 ml-2 rounded">
                        <img src="{{ asset('flip-cam.svg') }}" alt="Trocar câmera" style="max-width:none; width: 32px; height: 32px;">
                    </button>
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <div class="mb-4">
                <label class="block">CPF:</label>
                <input type="text" name="person[cpf]" class="border rounded w-full p-2"
                       value="{{ old('person.cpf', $benefitDelivery->person->cpf ?? '') }}">
            </div>

            <div class="mb-4">
                <label class="block">Nome:</label>
                <input type="text" name="person[name]" class="border rounded w-full p-2"
                       value="{{ old('person.name', $benefitDelivery->person->name ?? '') }}">
            </div>

            <div class="mb-4">
                <label class="block">Telefone:</label>
                <input type="text" name="person[phone]" class="border rounded w-full p-2"
                       value="{{ old('person.phone', $benefitDelivery->person->phone ?? '') }}">
            </div>

            <div class="mb-4">
                <label class="block">Benefício:</label>
                <select name="benefit_id" class="border rounded w-full p-2">
                    @foreach($benefits as $benefit)
                        <option value="{{ $benefit->id }}" {{ old('benefit_id', $benefitDelivery->benefit_id ?? '') == $benefit->id ? 'selected' : '' }}>
                            {{ $benefit->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- (Opcional) Campo unit_id se necessário -->
            <button type="submit" id="save-btn" class="w-full px-4 py-2 bg-indigo-500 text-white rounded">Salvar</button>
            <a class="w-full px-4 py-2 bg-gray-500 text-white rounded text-center mt-2" href="{{ route('benefit-deliveries.index') }}">
                Voltar
            </a>
        </div>
    </form>
</div>


@php
    $isEditing = isset($benefitDelivery) && $benefitDelivery->exists;
@endphp

@if(!$isEditing && !$errors->any())
    <!-- Modal de Busca -->
    <div id="searchModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="bg-white lg:p-6 px-3 py-4 rounded-lg shadow-lg w-96">
            <h2 class="text-lg font-semibold mb-4">Buscar Pessoa</h2>

            <label class="block mb-2">Digite o CPF ou Nome:</label>
            <input type="text" id="searchInput" autocomplete="off" class="border rounded w-full p-2" placeholder="Digite o CPF ou Nome">
            <div id="search-error" class="text-red-500 text-sm mt-2 hidden"></div>

            <div class="flex justify-between mt-4">
                <button id="closeSearchModal" class="px-4 py-2 bg-gray-500 text-white rounded">Cancelar</button>
                <button id="searchBtn" class="px-4 py-2 bg-blue-500 text-white rounded">Buscar</button>
            </div>

            <!-- Container da Tabela com Scroll -->
            <div id="resultContainer" class="hidden mt-4">
                <h3 class="text-md font-semibold mb-2">Resultados encontrados:</h3>
                <div class="overflow-y-auto max-h-60 border border-gray-300 rounded">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 p-2">Pessoa</th>
                            <th class="border border-gray-300 p-2 text-center">Ação</th>
                        </tr>
                        </thead>
                        <tbody id="resultTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById('benefit-delivery-register-form');
            const isEditing = @json($isEditing);
            const searchModal = document.getElementById("searchModal");
            const searchInput = document.getElementById("searchInput");
            const searchBtn = document.getElementById("searchBtn");
            const closeSearchModal = document.getElementById("closeSearchModal");
            const cpfInput = document.querySelector("input[name='person[cpf]']");
            const nomeInput = document.querySelector("input[name='person[name]']");
            const telefoneInput = document.querySelector("input[name='person[phone]']");
            const loadingOverlay = document.getElementById("loading-overlay");
            const searchError = document.getElementById("search-error");
            const submitBtn = document.getElementById("save-btn");


            // Exibir modal apenas se for novo registro
            if (!isEditing) {
                searchModal.classList.remove("hidden");
                searchInput.focus();
            }

            // Aplica máscara no CPF ao digitar
            searchInput?.addEventListener("input", function () {
                let value = searchInput.value;

                // Verifica se o primeiro caractere digitado é um número (indica CPF)
                if (/^\d/.test(value)) {
                    value = value.replace(/\D/g, ""); // Remove tudo que não for número

                    // Aplica a máscara do CPF
                    if (value.length > 3) value = value.replace(/^(\d{3})(\d)/, "$1.$2");
                    if (value.length > 6) value = value.replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3");
                    if (value.length > 9) value = value.replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, "$1.$2.$3-$4");

                    // Limita o campo a 14 caracteres no formato de CPF (000.000.000-00)
                    if (value.length > 14) {
                        value = value.slice(0, 14);
                    }
                }

                searchInput.value = value; // Atualiza o campo
            });

            searchInput?.addEventListener("keypress", async function (e) {
                if (e.key === "Enter") {
                    e.preventDefault(); // Evita o comportamento padrão (como submeter o formulário)
                    const filtro = searchInput.value.trim();

                    // Se o filtro tiver menos de 3 caracteres, mostra a mensagem de erro
                    if (filtro.length < 3) {
                        searchError.textContent = "Digite pelo menos 3 caracteres para a busca.";
                        searchError.classList.remove("hidden");
                        return;
                    } else {
                        // Se já estiver visível, oculta a mensagem ao digitar o suficiente
                        searchError.classList.add("hidden");
                    }

                    loadingOverlay.classList.remove("hidden"); // Exibe overlay de loading
                    await buscarPessoa(filtro);
                }
            });


            // Botão para buscar pessoa
            searchBtn?.addEventListener("click", async function () {
                const filtro = searchInput.value.trim();

                if (filtro.length < 3) {
                    searchError.textContent = "Digite pelo menos 3 caracteres para a busca.";
                    searchError.classList.remove("hidden");
                    return;
                } else {
                    // Se já estiver visível, oculta a mensagem ao digitar o suficiente
                    searchError.classList.add("hidden");
                }

                loadingOverlay.classList.remove("hidden"); // Exibe overlay de loading
                await buscarPessoa(filtro);
            });

            searchInput?.addEventListener("keypress", async function (e){
                if(e.code == ENTER){
                    const filtro = searchInput.value.trim();

                    if (filtro.length < 3) {
                        alert("Digite pelo menos 3 caracteres para a busca.");
                        return;
                    }

                    loadingOverlay.classList.remove("hidden"); // Exibe overlay de loading
                    await buscarPessoa(filtro);
                }
            });

            // Fechar modal e exibir formulário sem preenchimento
            if (closeSearchModal) {
                closeSearchModal.addEventListener("click", function () {
                    searchModal.classList.add("hidden");
                });
            }

            async function buscarPessoa(filtro) {
                const token = await obterToken();
                if (!token) {
                    loadingOverlay.classList.add("hidden");
                    return console.error("Autenticação falhou.");
                }

                let queryParam = (/^\d{11}$/.test(filtro.replace(/\D/g, '')))
                    ? `cpf=${filtro}`
                    : `nome=${encodeURIComponent(filtro)}`;

                fetch(`{{env('SIGVSA_API_BASE_URL')}}/pessoas?${queryParam}`, {
                    method: "GET",
                    headers: {
                        "Authorization": `Bearer ${token}`,
                        "Accept": "application/json"
                    }
                })
                    .then(response => {
                        if (!response.ok) throw new Error("Pessoa não encontrada");
                        return response.json();
                    })
                    .then(data => {
                        loadingOverlay.classList.add("hidden"); // Esconde overlay

                        if (data.length === 1) {
                            preencherFormulario(data[0]); // Preenche automaticamente se só houver 1 resultado
                            searchModal.classList.add("hidden");
                        } else if (data.length > 1) {
                            exibirResultados(data);
                        } else {
                            throw new Error("Nenhuma pessoa encontrada");
                        }
                    })
                    .catch(error => {
                        loadingOverlay.classList.add("hidden"); // Esconde overlay
                        console.error("Erro ao buscar pessoa:", error);

                        Swal.fire({
                            title: 'Atenção!',
                            text: "Nenhum registro encontrado para essa pesquisa. Solicite os dados para o cadastro.",
                            icon: 'info',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ok'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                cpfInput.focus();
                                searchModal.classList.add("hidden");
                            }
                        });
                    });
            }

            function exibirResultados(pessoas) {
                const resultTable = document.getElementById("resultTable");
                const resultContainer = document.getElementById("resultContainer");
                let paginaAtual = 1;
                const registrosPorPagina = 5;

                function renderizarTabela() {
                    resultTable.innerHTML = ""; // Limpa tabela
                    const inicio = (paginaAtual - 1) * registrosPorPagina;
                    const fim = inicio + registrosPorPagina;
                    const pagina = pessoas.slice(inicio, fim);

                    pagina.forEach(pessoa => {
                        let row = document.createElement("tr");
                        row.innerHTML = `
                <td class="border border-gray-300 p-2">
                    <span class="font-semibold">${pessoa.nome}</span><br>
                    <span class="text-sm text-gray-500">${pessoa.cpf}</span>
                </td>
                <td class="border border-gray-300 p-2 text-center">
                    <button class="select-btn bg-[cadetblue] text-white px-2 py-1 text-xs rounded" data-id="${pessoa.cpf}">
                        Selecionar
                    </button>
                </td>
            `;
                        resultTable.appendChild(row);
                    });

                    // Adiciona evento aos botões "Selecionar"
                    document.querySelectorAll(".select-btn").forEach(button => {
                        button.addEventListener("click", function () {
                            const cpf = this.getAttribute("data-id");
                            const pessoaSelecionada = pessoas.find(p => p.cpf === cpf);
                            preencherFormulario(pessoaSelecionada);
                            searchModal.classList.add("hidden");
                        });
                    });

                    atualizarPaginacao();
                }

                function atualizarPaginacao() {
                    const paginationDiv = document.getElementById("pagination");
                    if (!paginationDiv) {
                        const paginacao = document.createElement("div");
                        paginacao.id = "pagination";
                        paginacao.classList.add("flex", "justify-between", "mt-2");
                        resultContainer.appendChild(paginacao);
                    }

                    document.getElementById("pagination").innerHTML = `
            <button id="prevPage" class="px-3 py-1 bg-gray-500 text-white rounded ${paginaAtual === 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                Anterior
            </button>
            <span>Página ${paginaAtual} de ${Math.ceil(pessoas.length / registrosPorPagina)}</span>
            <button id="nextPage" class="px-3 py-1 bg-gray-500 text-white rounded ${paginaAtual * registrosPorPagina >= pessoas.length ? 'opacity-50 cursor-not-allowed' : ''}">
                Próxima
            </button>
        `;

                    document.getElementById("prevPage").addEventListener("click", () => {
                        if (paginaAtual > 1) {
                            paginaAtual--;
                            renderizarTabela();
                        }
                    });

                    document.getElementById("nextPage").addEventListener("click", () => {
                        if (paginaAtual * registrosPorPagina < pessoas.length) {
                            paginaAtual++;
                            renderizarTabela();
                        }
                    });
                }

                resultContainer.classList.remove("hidden");
                renderizarTabela();
            }



            function preencherFormulario(pessoa) {
                cpfInput.value = pessoa.cpf || "";
                nomeInput.value = pessoa.nome || "";
                telefoneInput.value = pessoa.telefone || "";
            }

            async function obterToken() {
                let token = localStorage.getItem("auth_token");

                if (!token) {
                    const response = await fetch("{{env('SIGVSA_API_BASE_URL')}}/login", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            email: "{{env('SIGVSA_API_EMAIL')}}",
                            password: "{{env('SIGVSA_API_PASSWORD')}}"
                        })
                    });

                    if (!response.ok) {
                        console.error("Erro ao obter token");
                        return null;
                    }

                    const data = await response.json();
                    token = data.token;
                    localStorage.setItem("auth_token", token);
                }

                return token;
            }

            if (form) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();

                    // Desabilita o botão e altera o texto
                    submitBtn.disabled = true;
                    const originalBtnText = submitBtn.innerText;
                    submitBtn.innerText = 'Enviando...';

                    // Exibe um alerta de carregamento (SweetAlert)
                    Swal.fire({
                        title: 'Enviando...',
                        text: 'Por favor, aguarde.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: form.method,
                            headers: {
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const responseData = await response.json();

                        // Fecha o loading antes de exibir a mensagem final
                        Swal.close();

                        if (!response.ok) {
                            // Reabilita o botão
                            submitBtn.disabled = false;
                            submitBtn.innerText = originalBtnText;

                            // Se houver erros de validação, exibe com Swal
                            let errorHtml = '<ul>';
                            if (responseData.errors) {
                                Object.values(responseData.errors).forEach(errors => {
                                    errors.forEach(msg => {
                                        errorHtml += `<li>${msg}</li>`;
                                    });
                                });
                                errorHtml += '</ul>';
                            } else {
                                errorHtml = `<p>${responseData.message || 'Ocorreu um erro.'}</p>`;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Erro de Validação',
                                html: errorHtml,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            // Em caso de sucesso, exibe a senha em destaque
                            const person = responseData.data.person;
                            const PicketCode = responseData.data.ticket_code;

                            Swal.fire({
                                icon: 'success',
                                title: 'Registro Efetuado!',
                                html: `
                        <p><strong>Senha:</strong>
                           <span style="font-size: 1.5rem; color: #D35400;">
                              ${PicketCode}
                           </span>
                        </p>
                        <p><strong>Nome:</strong> ${person.name}</p>
                        <p><strong>CPF:</strong> ${person.cpf}</p>
                        ${ person.phone ? `<p><strong>Telefone:</strong> ${person.phone}</p>` : '' }
                    `,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('benefit-deliveries.index') }}";
                                }
                            });
                        }
                    } catch (error) {
                        // Fecha o loading e reabilita o botão
                        Swal.close();
                        submitBtn.disabled = false;
                        submitBtn.innerText = originalBtnText;

                        console.error('Erro ao enviar formulário:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro Inesperado',
                            text: 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    </script>
@endpush
