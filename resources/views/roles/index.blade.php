<x-app-layout>
    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-bold mb-6">Controle de Acesso</h1>

        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('roles.index') }}" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('roles.*') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Papéis
                </a>
                <a href="{{ route('permissions.index') }}" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('permissions.*') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Permissões
                </a>
                <a href="{{ route('users.index') }}" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('users.*') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Usuários
                </a>
            </nav>
        </div>

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

        {{-- Conteúdo condicional --}}
        @if(request()->routeIs('roles.index'))
            <div class="mb-6">
                <a href="{{ route('roles.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">Novo Papel</a>
                <table class="table-auto w-full border">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2">Nome</th>
                        <th class="border px-4 py-2">Permissões</th>
                        <th class="border px-4 py-2">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td class="border px-4 py-2">{{ $role->name }}</td>
                            <td class="border px-4 py-2">
                                @foreach($role->permissions as $permission)
                                    <span class="bg-gray-200 text-sm px-2 py-1 rounded mr-1">{{ $permission->name }}</span>
                                @endforeach
                            </td>
                            <td class="border px-4 py-2">
                                <a href="{{ route('roles.edit', $role) }}" class="text-blue-500 hover:underline">Editar</a>
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline ml-2" onclick="return confirm('Tem certeza?')">Remover</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(request()->routeIs('permissions.index'))
            <div class="mb-6">
                <a href="{{ route('permissions.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">Nova Permissão</a>
                <table class="table-auto w-full border">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2">Nome</th>
                        <th class="border px-4 py-2">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($permissions as $permission)
                        <tr>
                            <td class="border px-4 py-2">{{ $permission->name }}</td>
                            <td class="border px-4 py-2">
                                <a href="{{ route('permissions.edit', $permission) }}" class="text-blue-500 hover:underline">Editar</a>
                                <form action="{{ route('permissions.destroy', $permission) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline ml-2" onclick="return confirm('Tem certeza?')">Remover</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
    @endif
</x-app-layout>
