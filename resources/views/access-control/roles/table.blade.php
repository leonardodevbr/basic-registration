<table class="min-w-full divide-y divide-gray-200 text-sm">
    <thead class="bg-gray-50">
    <tr>
        <th class="px-4 py-2 text-left font-medium text-gray-700">Nome</th>
        <th class="px-4 py-2 text-left font-medium text-gray-700">Permissões</th>
        <th class="px-4 py-2 text-right font-medium text-gray-700">Ações</th>
    </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-100">
    @foreach($roles as $role)
        <tr>
            <td class="px-4 py-2">{{ $role->name }}</td>
            <td class="px-4 py-2 cursor-pointer">
                {{-- Desktop --}}
                <div class="hidden md:flex flex-wrap gap-1 items-center group relative max-w-full"
                     @click="openPermissionsModal({{ $role->id }})">
                    @if($role->permissions->count() > 0)
                        @foreach($role->permissions->take(3) as $permission)
                            <span class="bg-gray-200 text-sm px-2 py-1 rounded">{{ $permission->name }}</span>
                        @endforeach

                        @if($role->permissions->count() > 3)
                            <span class="text-sm text-gray-500">+{{ $role->permissions->count() - 3 }} mais</span>
                        @endif
                    {{-- Tooltip (desktop) --}}
                    <span class="tooltip">Ver todas</span>
                    @else
                        <span class="bg-gray-200 text-sm px-2 py-1 rounded">Nenhuma</span>
                    @endif
                </div>

                {{-- Mobile --}}
                <div class="md:hidden flex items-center gap-2">
                    @if($role->permissions->count() > 0)
                        <button type="button"
                                class="text-blue-600 text-sm underline"
                                @click.stop="openPermissionsModal({{ $role->id }})">
                            Ver todas
                        </button>
                    @else
                        <span class="bg-gray-200 text-sm px-2 py-1 rounded">Nenhuma</span>
                    @endif
                </div>
            </td>

            <td class="px-4 py-2 text-right">
                <div class="flex justify-end items-center gap-3 relative dropdown-actions">
                    <a href="{{ route('roles.edit', $role) }}"
                       class="group text-indigo-600 hover:text-indigo-800 transition duration-200 transform hover:scale-110">
                        <i data-lucide="edit" class="w-5 h-5"></i>
                        <span class="tooltip">Editar</span>
                    </a>

                    <button onclick="toggleDropdown(this.closest('.dropdown-actions'))"
                            class="group text-gray-600 hover:text-gray-800 transition duration-200 transform hover:scale-110">
                        <i data-lucide="more-vertical" class="w-5 h-5"></i>
                        <span class="tooltip">Mais opções</span>
                    </button>

                    <div class="hidden dropdown-items top-[18px] absolute right-0 mt-2 bg-white shadow-lg border rounded-md w-32 z-10">
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition duration-200 transform hover:scale-105">
                                <i data-lucide="trash-2" class="w-4 h-4 inline-block mr-2"></i>Excluir
                            </button>
                        </form>
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
