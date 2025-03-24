{{-- resources/views/access-control/permissions/form.blade.php --}}
@if($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    // Agrupa os módulos únicos já cadastrados
    $existingModules = \Spatie\Permission\Models\Permission::select('module')
        ->distinct()
        ->orderBy('module')
        ->pluck('module')
        ->filter()
        ->toArray();

    $currentModule = old('module', $permission->module ?? '');
    $isCustom = $currentModule && !in_array($currentModule, $existingModules);
@endphp

<form action="{{ $action }}" method="POST" class="space-y-4" x-data="{ selected: '{{ $isCustom ? 'custom' : $currentModule }}', customModule: '{{ $isCustom ? $currentModule : '' }}' }">
    @csrf
    @method($method)

    <div class="flex w-full gap-x-2">
        <div class="w-full md:w-50">
            <label for="name" class="block font-medium text-sm text-gray-700">Nome da Permissão</label>
            <input type="text" name="name" id="name"
                   class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                   value="{{ old('name', $permission->name ?? '') }}" required>
        </div>

        <div class="w-full md:w-50">
            <label for="module" class="block font-medium text-sm text-gray-700">Módulo</label>
            <select x-model="selected"
                    name="module"
                    id="module"
                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    @change="if (selected !== 'custom') customModule = ''"
                    required>
                <option value="" disabled>Selecione um módulo</option>
                @foreach($existingModules as $mod)
                    <option value="{{ $mod }}" {{ $mod === $currentModule ? 'selected' : '' }}>
                        {{ ucfirst($mod) }}
                    </option>
                @endforeach
                <option value="custom" {{ $isCustom ? 'selected' : '' }}>Outro...</option>
            </select>

            {{-- Campo para módulo personalizado --}}
            <input type="text"
                   name="module"
                   x-model="customModule"
                   x-show="selected === 'custom'"
                   class="mt-2 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                   placeholder="Digite o nome do novo módulo"
                   x-ref="customInput"
                   x-init="if (selected === 'custom') $nextTick(() => $refs.customInput.focus())">
        </div>
    </div>

    <div class="flex justify-between items-center mt-6">
        <a href="{{ route('access-control.permissions') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancelar</a>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
    </div>
</form>
