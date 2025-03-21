<!-- resources/views/people/index.blade.php -->
<x-app-layout>
    <div class="md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav
                class="text-sm text-gray-500 my-2 ml-2"
                aria-label="Breadcrumb"
            >
                <ol class="list-reset flex">
                    <li>
                        <a
                            href="{{ route('dashboard') }}"
                            class="text-indigo-600 hover:text-indigo-800"
                        >Dashboard</a
                        >
                    </li>
                    <li><span class="mx-2">/</span></li>
                    <li>Benefícios entregues</li>
                </ol>
            </nav>
            <!-- Container Principal -->
            <div class="bg-white md:shadow-md md:rounded-md mf:p-6 px-3 py-6">
                <div class="flex justify-between mb-4 flex-col">
                    <h2 class="md:text-xl font-semibold text-gray-800">
                        Benefícios Entregues
                    </h2>
                    <div class="flex justify-between items-end">
                        <div class="flex flex-col">
                            <label for="sortSelect" class="text-sm text-gray-600">Ordenar por:</label>
                            <select id="sortSelect" class="border rounded px-2 py-1 text-sm">
                                <option value="default">Padrão</option>
                                <option value="status">Status</option>
                                <option value="id">ID</option>
                                <option value="name">Nome</option>
                                <option value="created_at">Data de Criação</option>
                            </select>
                        </div>
                        <div class="action-container">
                            <a
                                onclick="openQuickDeliveryModal()"
                                class="bg-orange-500 hover:bg-orange-600 text-center text-white ml-auto px-4 py-2 rounded">
                                Entrega
                            </a>
                            <a
                                href="{{ route('benefit-deliveries.create') }}"
                                class="bg-blue-500 hover:bg-blue-600 text-center text-white ml-2 px-4 py-2 rounded">
                                Novo
                            </a>
                        </div>
                    </div>
                </div>

                <form id="filter-form" class="flex items-start space-x-2 mb-2">
                    <!-- Input do filtro -->
                    <div class="relative w-full">
                        <div class="flex">
                            <input id="filter"
                                   type="text"
                                   name="filter"
                                   autocomplete="off"
                                   class="border rounded-l px-2 py-1 w-full"
                                   placeholder="Filtrar por senha, CPF ou nome"/>
                            <button type="submit" class="bg-[#1b1b18] hover:bg-[#1b1b02] text-center text-white px-4 py-2 rounded-r">Filtrar</button>
                        </div>
                        <label
                            id="filter-error"
                            class="text-red-600 text-sm mt-1 hidden"
                        ></label>
                    </div>
                </form>
                <div id="table-container" class="w-full w-full pb-3">
                    @include('benefit-deliveries.partials.table', ['benefitDeliveries' => $benefitDeliveries])
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Entrega Rápida -->
    <div
        id="quick-delivery-modal"
        class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden"
    >
        <div class="bg-white p-6 rounded-lg shadow relative w-80 max-w-md">
            <button
                onclick="closeQuickDeliveryModal()"
                class="absolute top-2 right-2 text-gray-500 text-2xl"
            >
                &times;
            </button>
            <h2 class="text-xl font-bold mb-4">Entrega Rápida</h2>
            <input
                type="text"
                inputmode="numeric"
                id="quick-delivery-code"
                placeholder="Digite o código do ticket"
                class="border rounded w-full p-2 mb-4"
                maxlength="6"
                autocomplete="off"
            />
            <button
                type="button"
                id="quick-delivery-button"
                onclick="quickDelivery()"
                class="relative w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 overflow-hidden"
            >
                <span id="quick-delivery-progress" class="absolute left-0 top-0 h-full w-0 bg-blue-700 opacity-50 transition-all duration-500"></span>
                <span id="quick-delivery-text">Processar</span>
            </button>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div
        id="details-modal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg relative w-80 max-w-md">
            <div id="modal-content" class="text-center">
                <!-- Placeholder da imagem -->
                <div class="flex justify-center relative group">
                    <!-- Imagem do usuário -->
                    <img
                        id="modalImage"
                        class="h-[160px] shadow rounded-full mx-auto mb-4 bg-gray-200 transition-transform duration-300 cursor-pointer"
                        src="/placeholder.png"
                        alt="Selfie"
                        onclick="openImageModal(this.src)"
                    />

                    <!-- Máscara de hover -->
                    <div
                        class="absolute h-[160px] w-[160px] bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                    >
                        <i data-lucide="expand" class="w-8 h-8 text-white"></i>
                    </div>
                </div>

                <!-- Informações do beneficiário -->
                <div class="flex flex-col items-center space-y-2">
                    <!-- Nome -->
                    <div class="relative flex items-center justify-center group">
                        <p id="modalName"
                           onclick="copyToClipboard(this)"
                           class="text-lg min-h-[28px] font-bold text-gray-900 cursor-pointer">
                        </p>
                        <i data-lucide="copy"
                           class="absolute right-[-22px] top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200 md:block hidden pointer-events-none">
                        </i>
                    </div>

                    <!-- Ticket Code -->
                    <div class="relative flex items-center justify-center group">
                        <p id="modalTicketCode"
                           onclick="copyToClipboard(this)"
                           class="text-lg h-[32px] font-semibold text-blue-600 cursor-pointer">
                        </p>
                        <i data-lucide="copy"
                           class="absolute right-[-22px] top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200 md:block hidden pointer-events-none">
                        </i>
                    </div>

                    <span
                        id="modalStatus"
                        class="text-xs h-[20px] font-medium px-3 rounded-full bg-gray-300 text-gray-800 animate-pulse min-w-[70px]"
                    ></span>

                    <!-- CPF -->
                    <div class="relative flex items-center justify-center group">
                        <p id="modalCpf"
                           onclick="copyToClipboard(this)"
                           class="text-sm h-[20px] text-gray-600 cursor-pointer">
                        </p>
                        <i data-lucide="copy"
                           class="absolute right-[-22px] top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200 md:block hidden pointer-events-none">
                        </i>
                    </div>

                    <!-- Telefone -->
                    <div class="relative flex items-center justify-center group">
                        <p id="modalPhone"
                           onclick="copyToClipboard(this)"
                           class="text-sm h-[20px] text-gray-600 cursor-pointer">
                        </p>
                        <i data-lucide="copy"
                           class="absolute right-[-22px] top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200 md:block hidden pointer-events-none">
                        </i>
                    </div>

                    <p
                        id="modalBenefit"
                        class="text-sm h-[20px] text-gray-600 bg-gray-200 min-w-[130px] px-4 rounded-md animate-pulse"
                    ></p>

                    <!-- Dados de Registro -->
                    <div
                        id="modalRegisteredSection"
                        class="hidden flex flex-col items-center text-gray-500 text-xs mt-2"
                    >
                        <small>Registro</small>
                        <p
                            id="modalRegisteredBy"
                            class="text-sm font-semibold text-gray-700"
                        ></p>
                        <p id="modalCreatedAt" class="text-xs"></p>
                    </div>

                    <!-- Dados de Entrega -->
                    <div
                        id="modalDeliveredSection"
                        class="hidden flex flex-col items-center text-gray-500 text-xs mt-2"
                    >
                        <small>Entrega</small>
                        <p
                            id="modalDeliveredBy"
                            class="text-sm font-semibold text-gray-700"
                        ></p>
                        <p id="modalDeliveredAt" class="text-xs"></p>
                    </div>
                </div>
            </div>
            <div id="modalActions" class="mt-4 flex flex-col gap-2 hidden"></div>
            <div class="mt-5 text-center">
                <button
                    onclick="closeModal()"
                    class="text-sm bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
                >
                    Fechar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para exibir a imagem em tamanho maior -->
    <div
        id="imageModal"
        class="fixed px-1 inset-0 flex items-center justify-center bg-black bg-opacity-75 hidden"
    >
        <div class="relative">
            <button
                onclick="closeImageModal()"
                class="absolute shadow-md top-2 right-2 bg-red-500 text-white text-2xl font-bold w-8 h-8 rounded-full hover:bg-red-100"
            >
                &times;
            </button>
            <img
                id="imageModalContent"
                class="max-w-full max-h-screen mx-auto rounded-md"
                alt="Imagem Ampliada"
            />
        </div>
    </div>
    @push('scripts')
        <script>
            const filterInput = document.getElementById("filter");
            const filterForm = document.getElementById("filter-form");
            const tableContainer = document.getElementById("table-container");
            const quickDeliveryModal = document.getElementById("quick-delivery-modal");
            const quickDeliveryCode = document.getElementById("quick-delivery-code");
            const urlParams = new URLSearchParams(window.location.search);
            const loadingOverlay = document.getElementById("loading-overlay");
            const filterError = document.getElementById("filter-error");
            const detailsModal = document.getElementById("details-modal");
            let emptyFilterSent = false;
            let lastSubmittedFilter = "";

            function copyToClipboard(el) {
                let text = el.textContent.trim();

                // Remove prefixos como "CPF: ", "Telefone: ", etc.
                if (text.includes(":")) {
                    text = text.split(":").slice(1).join(":").trim();
                }

                if (!text) return;

                navigator.clipboard.writeText(text).then(() => {
                    showCopyFeedback(el, 'Copiado!');
                }).catch(() => {
                    showCopyFeedback(el, 'Erro ao copiar');
                });
            }

            function showCopyFeedback(target, message) {
                // Remove qualquer feedback anterior
                const existing = target.parentElement.querySelector('.copy-feedback');
                if (existing) existing.remove();

                // Cria a div de feedback
                const feedback = document.createElement('div');
                feedback.textContent = message;
                feedback.className = "copy-feedback absolute -top-6 left-1/2 -translate-x-1/2 bg-black text-white text-xs px-2 py-1 rounded shadow z-50 opacity-0 transition-all duration-200";

                // Envolve o texto e o feedback num container relativo
                target.parentElement.classList.add('relative');
                target.parentElement.appendChild(feedback);

                // Força o fade-in
                requestAnimationFrame(() => {
                    feedback.classList.add('opacity-100');
                });

                // Remove após 1.5s
                setTimeout(() => {
                    feedback.classList.remove('opacity-100');
                    setTimeout(() => feedback.remove(), 200);
                }, 1500);
            }

            function openModal(benefitDeliveryId) {
                const modalImage = document.getElementById("modalImage");
                const placeholder = "/placeholder.png";

                // Exibir modal e resetar valores com placeholders
                detailsModal.classList.remove("hidden");
                modalImage.src = placeholder;

                // Lista de placeholders
                const placeholders = [
                    "modalName",
                    "modalTicketCode",
                    "modalCpf",
                    "modalPhone",
                    "modalBenefit",
                    "modalStatus",
                ];

                placeholders.forEach((id) => {
                    const element = document.getElementById(id);
                    element.innerText = "";
                    element.classList.add("animate-pulse", "bg-gray-200");
                });

                // Ocultar seções de registro e entrega até que sejam preenchidas
                document
                    .getElementById("modalRegisteredSection")
                    .classList.add("hidden");
                document
                    .getElementById("modalDeliveredSection")
                    .classList.add("hidden");

                fetch(`/benefit-deliveries/${benefitDeliveryId}`)
                    .then((response) => response.json())
                    .then((data) => {
                        const img = new Image();
                        img.src = data.person.selfie_url;

                        img.onload = function () {
                            modalImage.src = data.person.selfie_url;
                        };

                        img.onerror = function () {
                            modalImage.src = placeholder;
                        };

                        // Preenchendo os dados principais
                        updateModalField("modalName", data.person.name);
                        updateModalField(
                            "modalTicketCode",
                            data.ticket_code,
                            "text-blue-600 font-semibold",
                        );
                        updateModalField(
                            "modalCpf",
                            "<b>CPF:</b> " + formatCPF(data.person.cpf),
                            "",
                            true,
                        );
                        updateModalField(
                            "modalPhone",
                            "<b>Telefone:</b> " +
                            formatPhone(data.person.phone ?? ""),
                            "",
                            true,
                        );
                        updateModalField(
                            "modalBenefit",
                            "<b>Benefício:</b> " + data.benefit.name,
                            "",
                            true,
                        );

                        // 🔹 Atualizando status 🔹
                        const statusElement =
                            document.getElementById("modalStatus");
                        statusElement.className =
                            "text-xs h-[20px] min-w-[70px] font-medium px-3 rounded-full";
                        statusElement.innerText = translateStatus(data.status);
                        statusElement.classList.add(
                            ...getStatusBadge(data.status).split(" "),
                        );

                        addActionButtons(data.status, data.id);

                        // 🔹 Exibir dados extras apenas no mobile 🔹
                        if (window.innerWidth < 768) {
                            if (data.registered_by_id) {
                                updateModalField(
                                    "modalRegisteredBy",
                                    data.registered_by?.name ?? "Não informado",
                                );
                                updateModalField(
                                    "modalCreatedAt",
                                    formatDateTime(data.created_at),
                                );
                                document
                                    .getElementById("modalRegisteredSection")
                                    .classList.remove("hidden");
                            }

                            if (data.delivered_by_id) {
                                updateModalField(
                                    "modalDeliveredBy",
                                    data.delivered_by?.name ?? "Não informado",
                                );
                                updateModalField(
                                    "modalDeliveredAt",
                                    formatDateTime(data.delivered_at),
                                );
                                document
                                    .getElementById("modalDeliveredSection")
                                    .classList.remove("hidden");
                            }
                        }

                        // 🔹 Evento de clique para abrir a imagem em tamanho maior 🔹
                        modalImage.onclick = function () {
                            openImageModal(data.person.selfie_url);
                        };
                    })
                    .catch((error) => {
                        console.error("Erro ao carregar os detalhes:", error);
                        alert("Erro ao carregar os detalhes. Tente novamente.");
                    });
            }

            function addActionButtons(status, benefitDeliveryId) {
                const actionsContainer = document.getElementById("modalActions");
                actionsContainer.innerHTML = ""; // Limpa botões antigos

                if (status === "PENDING") {
                    actionsContainer.innerHTML += `
            <button onclick="confirmDelivery(${benefitDeliveryId})"
                class="text-sm bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                Confirmar Entrega
            </button>
        `;
                } else if (status === "EXPIRED") {
                    actionsContainer.innerHTML += `
            <button onclick="reissueTicket(${benefitDeliveryId})"
                class="text-sm bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 w-full">
                Reemitir Benefício
            </button>
        `;
                } else if (status === "DELIVERED") {
                    actionsContainer.innerHTML += `
            <button onclick="viewReceipt(${benefitDeliveryId})"
                class="text-sm bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
                Ver Comprovante
            </button>
        `;
                }

                if (actionsContainer.innerHTML !== "") {
                    actionsContainer.classList.remove("hidden"); // Exibe os botões se houver algum
                }
            }

            function viewReceipt(id) {
                window.open(`/benefit-deliveries/${id}/receipt`, "_blank");
            }

            // 🔹 Função para abrir a imagem expandida 🔹
            function openImageModal(imageUrl) {
                const imageModal = document.getElementById("imageModal");
                const imageModalContent =
                    document.getElementById("imageModalContent");

                imageModalContent.src = imageUrl;
                imageModal.classList.remove("hidden");
            }

            // 🔹 Função para fechar o modal da imagem 🔹
            function closeImageModal() {
                document.getElementById("imageModal").classList.add("hidden");
            }

            // 🔹 Função auxiliar para formatar data e hora 🔹
            function formatDateTime(dateTime) {
                if (!dateTime) return "Não informado";
                const date = new Date(dateTime);
                return date.toLocaleString("pt-BR", {
                    day: "2-digit",
                    month: "2-digit",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                });
            }

            // Atualiza os campos do modal removendo os placeholders
            function updateModalField(id, text, extraClass = "", useHTML = false) {
                const element = document.getElementById(id);
                if (useHTML) {
                    element.innerHTML = text; // Permite HTML dentro do elemento
                } else {
                    element.innerText = text; // Mantém apenas texto puro
                }
                element.classList.remove("animate-pulse", "bg-gray-200");
                if (extraClass) element.classList.add(...extraClass.split(" "));
            }

            function closeModal() {
                const modalImage = document.getElementById("modalImage");
                const placeholder = "/placeholder.png";

                // Esconde o modal
                detailsModal.classList.add("hidden");

                // Resetando imagem para placeholder
                modalImage.src = placeholder;

                // Lista de elementos a serem resetados
                const placeholders = [
                    "modalName",
                    "modalTicketCode",
                    "modalCpf",
                    "modalPhone",
                    "modalBenefit",
                    "modalStatus",
                ];

                placeholders.forEach((id) => {
                    const element = document.getElementById(id);
                    element.innerText = "";

                    // Remove classes antigas antes de adicionar novas
                    element.classList.remove(
                        "bg-gray-200",
                        "rounded-md",
                        "animate-pulse",
                    );

                    // Adiciona classes corretamente separadas
                    element.classList.add(
                        "bg-gray-200",
                        "rounded-md",
                        "animate-pulse",
                    );
                });

                // 🔹 Resetando especificamente o status para evitar cor errada
                const statusElement = document.getElementById("modalStatus");
                statusElement.innerText = "";
                statusElement.className =
                    "text-xs h-[20px] min-w-[70px] font-medium px-3 rounded-full bg-gray-300 text-gray-800 animate-pulse";
            }

            // Formatar CPF
            function formatCPF(cpf) {
                return cpf.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4");
            }

            // Formatar telefone
            function formatPhone(phone) {
                phone = phone.replace(/\D/g, "");
                return phone.length === 11
                    ? phone.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3")
                    : phone.replace(/^(\d{2})(\d{4})(\d{4})$/, "($1) $2-$3");
            }

            // Traduzir status
            function translateStatus(status) {
                const statuses = {
                    PENDING: "Pendente",
                    DELIVERED: "Entregue",
                    EXPIRED: "Expirado",
                    REISSUED: "Reemitido",
                };
                return statuses[status] || status;
            }

            // Definir classe de badge conforme o status
            function getStatusBadge(status) {
                const badges = {
                    PENDING: "bg-blue-100 text-blue-800",
                    DELIVERED: "bg-green-100 text-green-800",
                    EXPIRED: "bg-red-100 text-red-800",
                    REISSUED: "bg-gray-200 text-gray-800",
                };
                return badges[status] || "bg-gray-300 text-gray-800";
            }

            function confirmDelivery(deliveryId) {
                Swal.fire({
                    title: "Confirmar Entrega?",
                    text: "Tem certeza de que deseja dar baixa nesta entrega?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sim, dar baixa!",
                    cancelButtonText: "Cancelar",
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/benefit-deliveries/${deliveryId}/deliver`, {
                            method: "PATCH",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                Accept: "application/json",
                            },
                        })
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.success) {
                                    detailsModal.classList.add('hidden');
                                    Swal.fire({
                                        icon: "success",
                                        title: "Entrega Confirmada",
                                        text: data.message,
                                        confirmButtonText: "OK",
                                    }).then(() => {
                                        if (typeof lucide !== "undefined") {
                                            lucide.createIcons();
                                        } else {
                                            console.error("Lucide não carregado corretamente.");
                                        }

                                        // Reanexar eventos
                                        reloadTableContent(window.location.href);
                                        attachRowsEvents();
                                        attachDeleteEvents();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Erro",
                                        text: data.message,
                                        confirmButtonText: "OK",
                                    });
                                }
                            })
                            .catch((error) => {
                                console.error(
                                    "Erro ao dar baixa na entrega:",
                                    error,
                                );
                                Swal.fire({
                                    icon: "error",
                                    title: "Erro Inesperado",
                                    text: "Ocorreu um erro inesperado. Tente novamente mais tarde.",
                                    confirmButtonText: "OK",
                                });
                            });
                    }
                });
            }

            function reissueTicket(benefitId) {
                Swal.fire({
                    title: "Reemitir Ticket?",
                    text: "Tem certeza de que deseja gerar um novo ticket para esta entrega?",
                    icon: "warning",
                    showCancelButton: true,
                    cancelButtonText: "Cancelar",
                    confirmButtonText: "Sim, reemitir!",
                    confirmButtonColor: "#f59e0b", // Amarelo
                    cancelButtonColor: "#6c757d", // Cinza
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        detailsModal.classList.add('hidden');
                        fetch(`/benefit-deliveries/${benefitId}/reissue`, {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute("content"),
                                Accept: "application/json",
                                "Content-Type": "application/json",
                            },
                        })
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Ticket Reemitido",
                                        text: data.message,
                                        confirmButtonText: "OK",
                                    });

                                    // 🔄 Recarregar apenas a tabela
                                    reloadTableContent(window.location.href);
                                } else {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Erro",
                                        text: data.message,
                                        confirmButtonText: "OK",
                                    });
                                }
                            })
                            .catch((error) => {
                                console.error("Erro ao reemitir ticket:", error);
                                Swal.fire({
                                    icon: "error",
                                    title: "Erro Inesperado",
                                    text: "Ocorreu um erro inesperado. Tente novamente mais tarde.",
                                    confirmButtonText: "OK",
                                });
                            });
                    }
                });
            }

            function attachDeleteEvents() {
                document.querySelectorAll(".delete-form").forEach((form) => {
                    form.addEventListener("submit", function (e) {
                        e.preventDefault(); // Impede o envio padrão do formulário

                        const row = this.closest("tr"); // Obtém a linha da tabela
                        const actionUrl = this.action;

                        Swal.fire({
                            title: "Confirmar Exclusão?",
                            text: "Tem certeza de que deseja excluir este registro? Essa ação não pode ser desfeita.",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#d33",
                            cancelButtonColor: "#6c757d",
                            confirmButtonText: "Sim, excluir!",
                            cancelButtonText: "Cancelar",
                            reverseButtons: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch(actionUrl, {
                                    method: "DELETE",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                        Accept: "application/json",
                                    },
                                })
                                    .then((response) => response.json())
                                    .then((data) => {
                                        if (data.success) {
                                            attachPaginationEvents();
                                            attachRowsEvents();
                                            attachDeleteEvents();
                                            attachFilterEvents();
                                            row.remove();
                                            Swal.fire({
                                                icon: "success",
                                                title: "Registro Excluído",
                                                text: data.message,
                                                confirmButtonText: "OK",
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: "error",
                                                title: "Erro",
                                                text:
                                                    data.message ||
                                                    "Não foi possível excluir o registro.",
                                                confirmButtonText: "OK",
                                            });
                                        }
                                    })
                                    .catch((error) => {
                                        console.error("Erro ao excluir:", error);
                                        Swal.fire({
                                            icon: "error",
                                            title: "Erro Inesperado",
                                            text: "Ocorreu um erro inesperado. Tente novamente.",
                                            confirmButtonText: "OK",
                                        });
                                    });
                            }
                        });
                    });
                });
            }

            function attachFilterEvents() {
                filterForm.addEventListener("submit", async function (e) {
                        e.preventDefault();
                        filterInput.blur();

                        const filter = document
                            .getElementById("filter")
                            .value.trim();

                        // 🔥 Bloqueia pesquisas com menos de 3 caracteres, exceto quando limpando
                        if (filter.length < 3) {
                            Swal.fire({
                                icon: "warning",
                                title: "Atenção",
                                text: "Digite pelo menos 3 caracteres para pesquisar.",
                                confirmButtonText: "OK",
                            });
                            return;
                        }

                        try {
                            const response = await fetch(
                                "{{ route('benefit-deliveries.filter') }}?filter=" +
                                encodeURIComponent(filter),
                                {
                                    headers: {Accept: "application/json"},
                                },
                            );

                            if (!response.ok) {
                                throw new Error(
                                    "Erro na requisição: " + response.statusText,
                                );
                            }

                            const data = await response.json();

                            if (data.success) {
                                document.getElementById(
                                    "deliveries-table-body",
                                ).innerHTML = data.html;

                                if (typeof lucide !== "undefined") {
                                    lucide.createIcons();
                                } else {
                                    console.error("Lucide não carregado corretamente.");
                                }

                                // 🔥 Remove a paginação quando há um filtro ativo
                                document.getElementById(
                                    "pagination-links",
                                ).innerHTML = "";

                                // Reanexar eventos
                                attachPaginationEvents();
                                attachRowsEvents();
                                attachDeleteEvents();
                                attachFilterEvents();
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Erro",
                                    text:
                                        data.message ||
                                        "Falha ao filtrar os registros.",
                                });
                            }
                        } catch (error) {
                            console.error("Erro ao filtrar:", error);
                            Swal.fire({
                                icon: "error",
                                title: "Erro",
                                text: "Não foi possível carregar os registros filtrados. Tente novamente.",
                                confirmButtonText: "OK",
                            });
                        }
                    });
            }

            function attachRowsEvents() {
                document.querySelectorAll("tr").forEach((row) => {
                    let pressTimer;

                    row.addEventListener("touchstart", function (e) {
                        if (e.target.classList.contains("dropdown-button")) {
                            toggleDropdown(e.target.closest('.dropdown-actions'));
                            return;
                        }

                        // Inicia o temporizador para detectar um press longo
                        pressTimer = setTimeout(() => {
                            const dropdown = row.querySelector(".dropdown-actions");
                            if (dropdown) {
                                toggleDropdown(dropdown);
                            }
                        }, 300); // Tempo de pressão longa
                    });

                    row.addEventListener("touchend", function () {
                        clearTimeout(pressTimer);
                    });

                    row.addEventListener("touchmove", function () {
                        clearTimeout(pressTimer); // Cancela o evento se o usuário deslizar o dedo
                    });
                });
            }

            function attachPaginationEvents() {
                document.querySelectorAll("#pagination-links a").forEach((link) => {
                    link.addEventListener("click", function (e) {
                        e.preventDefault();
                        let newUrl = this.href;
                        history.pushState(null, "", newUrl);
                        reloadTableContent(newUrl);
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
                quickDeliveryModal
                    .classList.remove("hidden");
                quickDeliveryCode.focus();
            }

            function closeQuickDeliveryModal() {
                quickDeliveryModal
                    .classList.add("hidden");
            }

            async function quickDelivery() {
                const codeInput = document.getElementById("quick-delivery-code");
                const code = codeInput.value.trim();
                const button = document.getElementById("quick-delivery-button");
                const progressBar = document.getElementById("quick-delivery-progress");
                const buttonText = document.getElementById("quick-delivery-text");

                if (code.length !== 6) {
                    Swal.fire({
                        icon: "warning",
                        title: "Atenção",
                        text: "Insira um código de 6 dígitos.",
                        confirmButtonText: "OK",
                    });
                    return;
                }

                try {
                    // Desativa o botão e inicia a animação da barra de progresso
                    button.disabled = true;
                    buttonText.innerText = "Processando...";
                    progressBar.style.width = "100%"; // Simula o preenchimento

                    const response = await fetch("{{ route('benefit-deliveries.quick-deliver') }}", {
                        method: "PATCH",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            Accept: "application/json",
                        },
                        body: JSON.stringify({ ticket_code: code }),
                    });

                    if (!response.ok) {
                        const errorData = await response.json(); // Captura o JSON antes de lançar o erro
                        throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();

                    // 🚀 Se o backend retornou `success: false`, lança erro manualmente
                    if (!data.success) {
                        throw new Error(data.message || "Erro ao registrar a baixa.");
                    }

                    // ✅ Sucesso: exibe mensagem e recarrega a tabela
                    Swal.fire({
                        icon: "success",
                        title: "Baixa Registrada",
                        text: data.message,
                        confirmButtonText: "OK",
                    }).then(() => {
                        // Limpa o input e reseta a tabela
                        codeInput.value = "";
                        loadingOverlay.classList.remove("hidden");
                        fetch("{{ route('benefit-deliveries.index') }}", {
                            headers: {"X-Requested-With": "XMLHttpRequest"},
                        })
                            .then((response) => response.json())
                            .then((data) => {
                                tableContainer.innerHTML = data.html;
                            })
                            .finally(() => {
                                loadingOverlay.classList.add("hidden");
                                quickDeliveryCode.focus();
                            });

                        // Restaura o botão ao estado inicial
                        resetButton(button, progressBar, buttonText);
                    });

                } catch (error) {
                    console.error("Erro:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Erro",
                        text: error.message || "Ocorreu um erro inesperado. Tente novamente mais tarde.",
                        confirmButtonText: "OK",
                    });

                    // Restaura o botão ao estado original
                    resetButton(button, progressBar, buttonText);
                }
            }

            // ✅ Função para restaurar o botão ao estado original
            function resetButton(button, progressBar, buttonText) {
                setTimeout(() => {
                    progressBar.style.width = "0"; // Esvazia a barra de progresso
                    buttonText.innerText = "Processar";
                    button.disabled = false;
                }, 500);
            }

            function toggleDropdown(dropdown) {
                let button = dropdown.querySelector('.dropdown-button');
                let items = dropdown.querySelector('.dropdown-items');

                // Fecha qualquer outro dropdown aberto
                document.querySelectorAll(".dropdown-items").forEach((el) => {
                    if (el !== items) {
                        el.classList.add("hidden");
                    }
                });

                // Fecha ao clicar fora
                document.addEventListener("click", function closeDropdown(event) {
                    if (
                        !button.contains(event.target) &&
                        !items.contains(event.target)
                    ) {
                        items.classList.add("hidden");
                        document.removeEventListener("click", closeDropdown);
                    }
                });

                items.classList.remove("hidden");
            }

            function reloadTableContent(url) {
                window.scrollTo({ top: 0, behavior: "smooth" });

                fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            document.getElementById("table-container").innerHTML = data.html;
                            if (typeof lucide !== "undefined") {
                                lucide.createIcons();
                            } else {
                                console.error("Lucide não carregado corretamente.");
                            }

                            // Reanexar eventos
                            attachPaginationEvents();
                            attachRowsEvents();
                            attachDeleteEvents();
                            attachFilterEvents();
                        } else {
                            console.error("Erro ao carregar tabela:", data);
                            Swal.fire({
                                icon: "error",
                                title: "Erro",
                                text: "Ocorreu um erro ao carregar a tabela.",
                                confirmButtonText: "OK",
                            });
                        }
                    })
                    .catch((error) => console.error("Erro ao recarregar a tabela:", error));
            }

            // ** Garante que os eventos são reanexados após cada atualização da tabela **
            document.addEventListener("DOMContentLoaded", function () {
                if (typeof lucide !== "undefined") {
                    lucide.createIcons();
                } else {
                    console.error("Lucide não carregado corretamente.");
                }

                if (urlParams.has("filter")) {
                    filterInput.value = urlParams.get("filter");
                }

                quickDeliveryCode.addEventListener("keypress", function (e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        const code = quickDeliveryCode.value.trim();
                        if (code.length === 6) {
                            quickDelivery(); // Chama a função de baixa rápida
                        } else {
                            Swal.fire({
                                icon: "warning",
                                title: "Atenção",
                                text: "Insira um código de 6 dígitos.",
                                confirmButtonText: "OK",
                            });
                        }
                    }
                });

                quickDeliveryCode.addEventListener("keypress", function (e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        quickDelivery();
                    }
                });

                filterInput?.addEventListener("input", function () {
                    let value = filterInput.value;

                    // Se o valor contiver apenas dígitos, aplica a máscara do CPF se tiver mais de 6 dígitos
                    if (/^\d/.test(value)) {
                        value = value.replace(/\D/g, ""); // Remove tudo que não for número

                        if (value.length > 6)
                            value = value.replace(/^(\d{3})(\d)/, "$1.$2");
                        if (value.length > 6)
                            value = value.replace(
                                /^(\d{3})\.(\d{3})(\d)/,
                                "$1.$2.$3",
                            );
                        if (value.length > 9)
                            value = value.replace(
                                /^(\d{3})\.(\d{3})\.(\d{3})(\d)/,
                                "$1.$2.$3-$4",
                            );

                        if (value.length > 14) {
                            value = value.slice(0, 14);
                        }
                    }

                    filterInput.value = value; // Atualiza o campo

                    // Se o campo for limpo, reseta lastSubmittedFilter e dispara o reset apenas uma vez
                    if (value.trim() === "") {
                        lastSubmittedFilter = "";
                        if (!emptyFilterSent) {
                            filterInput.blur();
                            emptyFilterSent = true;
                            filterError.classList.add("hidden");
                            loadingOverlay.classList.remove("hidden");
                            fetch("{{ route('benefit-deliveries.index') }}", {
                                headers: {"X-Requested-With": "XMLHttpRequest"},
                            })
                                .then((response) => response.json())
                                .then((data) => {
                                    tableContainer.innerHTML = data.html;
                                })
                                .finally(() => {
                                    loadingOverlay.classList.add("hidden");
                                    attachFilterEvents(); // Chamar a função ao carregar a página
                                    attachPaginationEvents(); // Chamar a função ao carregar a página
                                    attachRowsEvents();
                                    attachDeleteEvents(); // Chama a função ao carregar a página
                                });
                        }
                    } else {
                        // Se houver conteúdo, reseta a flag de reset e o último filtro enviado
                        emptyFilterSent = false;
                        lastSubmittedFilter = "";
                    }
                });

                document.getElementById("sortSelect").addEventListener("change", function () {
                    const sortBy = this.value;
                    const sortOrder = sortBy === "id" || sortBy === "created_at" ? "desc" : "asc"; // Padrão: ID e Data em DESC, outros em ASC

                    reloadTableContent(`?sort_by=${sortBy}&sort_order=${sortOrder}`);
                });

                attachFilterEvents(); // Chamar a função ao carregar a página
                attachPaginationEvents(); // Chamar a função ao carregar a página
                attachRowsEvents();
                attachDeleteEvents(); // Chama a função ao carregar a página
            });
        </script>
    @endpush
</x-app-layout>
