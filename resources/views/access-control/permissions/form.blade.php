{{--resources/views/access-control/permissions/form.blade.php--}}
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
        <label for="name" class="block font-medium text-sm text-gray-700">Nome da Permissão</label>
        <input type="text" name="name" id="name"
               class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
               value="{{ old('name', $permission->name ?? '') }}" required>
    </div>

    <div class="flex justify-between items-center mt-6">
        <a href="{{ route('access-control.permissions') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancelar</a>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
    </div>
</form>
