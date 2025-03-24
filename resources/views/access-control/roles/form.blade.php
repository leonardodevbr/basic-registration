@if($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @method($method)

    <div>
        <label for="name" class="block font-medium text-sm text-gray-700">Nome do Papel</label>
        <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" value="{{ old('name', $role->name ?? '') }}" required>
    </div>

    <div>
        <label class="block font-medium text-sm text-gray-700 mb-2">Permissões</label>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($permissions->groupBy('module') as $module => $grouped)
                <div class="space-y-2">
                    <h4 class="text-sm font-semibold text-gray-600">{{ $module ?? 'Sem módulo' }}</h4>
                    @foreach($grouped as $permission)
                        <label class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->id }}"
                                {{ isset($role) && $role->permissions->contains($permission->id) ? 'checked' : '' }}
                            >
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>


    <div class="flex justify-between items-center mt-6">
        <a href="{{ route('access-control.roles') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancelar</a>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
    </div>
</form>
