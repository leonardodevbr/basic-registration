@if($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" class="space-y-6">
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
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                           class="role-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200"
                           data-role-id="{{ $role->id }}"
                        {{ in_array($role->id, old('roles', isset($user) ? $user->roles->pluck('id')->toArray() : [])) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">{{ $role->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="block font-medium text-sm text-gray-700 mb-2">Permissões Selecionadas</label>
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
                                class="permission-checkbox"
                                data-permission-id="{{ $permission->id }}"
                                data-modules="{{ $permission->module }}"
                                {{ in_array($permission->id, old('permissions', isset($user) ? $user->permissions->pluck('id')->toArray() : [])) ? 'checked' : '' }}>
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex justify-between items-center mt-6">
        <a href="{{ route('access-control.users') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancelar</a>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const roleCheckboxes = document.querySelectorAll('.role-checkbox');
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
        const rolePermissions = @json($roles->mapWithKeys(fn($role) => [$role->id => $role->permissions->pluck('id')]));

        roleCheckboxes.forEach(roleCheckbox => {
            roleCheckbox.addEventListener('change', function () {
                const roleId = this.getAttribute('data-role-id');
                const permissionsForRole = rolePermissions[roleId] || [];

                permissionCheckboxes.forEach(checkbox => {
                    const permissionId = parseInt(checkbox.value);
                    if (permissionsForRole.includes(permissionId)) {
                        checkbox.checked = this.checked;
                    }
                });
            });
        });
    });
</script>
