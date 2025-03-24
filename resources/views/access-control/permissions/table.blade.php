<table class="min-w-full divide-y divide-gray-200 text-sm">
    <thead class="bg-gray-50">
    <tr>
        <th class="px-4 py-2 text-left font-medium text-gray-700">Nome</th>
        <th class="px-4 py-2 text-right font-medium text-gray-700">Ações</th>
    </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-100">
    @foreach($permissions as $permission)
        <tr>
            <td class="px-4 py-2">{{ $permission->name }}</td>
            <td class="px-4 py-2 text-right">
                <div class="flex justify-end items-center gap-3 relative dropdown-actions">
                    <a href="{{ route('permissions.edit', $permission) }}"
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
                        <form action="{{ route('permissions.destroy', $permission) }}" method="POST" class="delete-form">
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
