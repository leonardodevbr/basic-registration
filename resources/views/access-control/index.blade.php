{{-- resources/views/access-control/index.blade.php --}}
@section('title', 'Controle de Acesso')
<x-app-layout>
    <div class="md:py-12"
         x-data="accessControlTabs('{{ $tab }}')"
         x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Breadcrumbs --}}
            <nav class="text-sm text-gray-500 my-2 ml-2" aria-label="Breadcrumb">
                <ol class="list-reset flex">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Dashboard</a>
                    </li>
                    <li><span class="mx-2">/</span></li>
                    <li>Controle de Acesso</li>
                </ol>
            </nav>

            {{-- Cartão principal --}}
            <div class="bg-white md:shadow-md md:rounded-md mf:p-6 px-3 py-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Controle de Acesso</h2>

                {{-- Mensagens de sucesso --}}
                @if(session('success'))
                    <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('permission_success'))
                    <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
                        {{ session('permission_success') }}
                    </div>
                @endif

                {{-- Abas --}}
                <div class="mb-6 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button @click="setTab('roles')" :class="tab === 'roles' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                            Papéis
                        </button>
                        <button @click="setTab('permissions')" :class="tab === 'permissions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                            Permissões
                        </button>
                        <button @click="setTab('users')" :class="tab === 'users' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                            Usuários
                        </button>
                    </nav>
                </div>

                {{-- Painel de Papéis --}}
                <div x-cloak x-show="tab === 'roles'" x-transition>
                    <div class="mb-6">
                        @can('create role')
                            <a href="{{ route('roles.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">Novo Papel</a>
                        @endcan
                        <div class="overflow-x-scroll">
                            @include('access-control.roles.table')
                        </div>
                    </div>
                </div>

                {{-- Painel de Permissões --}}
                <div x-cloak x-show="tab === 'permissions'" x-transition>
                    <div class="mb-6">
                        @can('create permission')
                            <a href="{{ route('permissions.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">Nova Permissão</a>
                        @endcan
                        <div class="overflow-x-scroll">
                            @include('access-control.permissions.table')
                        </div>
                    </div>
                </div>

                {{-- Painel de Usuários --}}
                <div x-cloak x-show="tab === 'users'" x-transition>
                    <div class="mb-6">
                        @can('create user')
                            <a href="{{ route('users.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">Novo Usuário</a>
                        @endcan
                        <div class="overflow-x-scroll">
                            @include('access-control.users.table')
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Modal de Permissões (corrigido) --}}
        <div x-show="permissionsModal.open"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50"
             @keydown.escape.window="permissionsModal.open = false"
        >
            <div class="bg-white rounded-md shadow-lg max-w-md w-full p-6 relative">
                <button @click="permissionsModal.open = false"
                        class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    Permissões de <span x-text="permissionsModal.roleName"></span>
                </h3>
                <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 max-h-64 overflow-y-auto">
                    <template x-for="permission in permissionsModal.permissions" :key="permission.name">
                        <li x-text="permission.name"></li>
                    </template>
                </ul>
            </div>
        </div>

    </div>

    {{-- Scripts --}}
    <script>
        function toggleDropdown(container) {
            container.querySelectorAll('.dropdown-items').forEach(el => {
                el.classList.toggle('hidden');
            });
        }

        document.addEventListener('click', function (e) {
            document.querySelectorAll('.dropdown-items').forEach(drop => {
                if (!drop.closest('.dropdown-actions')?.contains(e.target)) {
                    drop.classList.add('hidden');
                }
            });
        });

        function accessControlTabs(initialTab) {
            return {
                tab: initialTab,

                // Modal de Permissões
                permissionsModal: {
                    open: false,
                    permissions: [],
                    roleName: ''
                },

                init() {
                    this.updateUrl();
                },

                setTab(newTab) {
                    this.tab = newTab;
                    this.updateUrl();
                },

                updateUrl() {
                    let url = '';
                    switch (this.tab) {
                        case 'permissions':
                            url = '{{ route('access-control.permissions') }}';
                            break;
                        case 'users':
                            url = '{{ route('access-control.users') }}';
                            break;
                        default:
                            url = '{{ route('access-control.roles') }}';
                    }
                    window.history.pushState({}, '', url);
                },

                openPermissionsModal(roleId) {
                    const roles = @json($roles);
                    const role = roles.find(r => r.id === roleId);
                    if (role) {
                        this.permissionsModal.roleName = role.name;
                        this.permissionsModal.permissions = role.permissions;
                        this.permissionsModal.open = true;
                    }
                }
            }
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
                        allowOutsideClick: false, // 🔒 Impede clique fora
                        allowEscapeKey: false,    // 🔒 Impede ESC fechar
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
                                    "X-Requested-With": "XMLHttpRequest", // 👈 isso faz o Laravel reconhecer como AJAX
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                    "Accept": "application/json",
                                },
                            })
                                .then((response) => response.json())
                                .then((data) => {
                                    if (data.success) {
                                        row.remove();
                                        Swal.fire({
                                            allowOutsideClick: false, // 🔒 Impede clique fora
                                            allowEscapeKey: false,    // 🔒 Impede ESC fechar
                                            icon: "success",
                                            title: "Registro Excluído",
                                            text: data.message,
                                            confirmButtonText: "OK",
                                        });
                                    } else {
                                        Swal.fire({
                                            allowOutsideClick: false, // 🔒 Impede clique fora
                                            allowEscapeKey: false,    // 🔒 Impede ESC fechar
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
                                        allowOutsideClick: false, // 🔒 Impede clique fora
                                        allowEscapeKey: false,    // 🔒 Impede ESC fechar
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
    </script>

    {{-- Lucide icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            attachDeleteEvents();
        });
    </script>
</x-app-layout>
