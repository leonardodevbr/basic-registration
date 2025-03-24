{{-- resources/views/access-control/users/form.blade.php --}}
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
        <label for="name" class="block font-medium text-sm text-gray-700">Nome</label>
        <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" value="{{ old('name', $user->name ?? '') }}" required>
    </div>

    <div>
        <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
        <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" value="{{ old('email', $user->email ?? '') }}" required>
    </div>

    <div>
        <label for="password" class="block font-medium text-sm text-gray-700">Senha</label>
        <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
    </div>

    <div>
        <label class="block font-medium text-sm text-gray-700 mb-2">Papéis</label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            @foreach($roles as $role)
                <label class="inline-flex items-center">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" {{ isset($user) && $user->roles->contains($role->id) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200">
                    <span class="ml-2 text-sm text-gray-700">{{ $role->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="flex justify-between items-center mt-6">
        <a href="{{ route('access-control.users') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancelar</a>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
    </div>
</form>
