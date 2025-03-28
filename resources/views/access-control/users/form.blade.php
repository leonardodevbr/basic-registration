@php
    $directPermissions = old('permissions', isset($user) ? $user->permissions->pluck('id')->toArray() : []);
    $inheritedPermissions = $inheritedPermissions ?? (
        isset($user)
            ? $user->roles->flatMap(fn($role) => $role->permissions)->pluck('id')->unique()->toArray()
            : []
    );
@endphp

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
        <label for="registration_number" class="block font-medium text-sm text-gray-700">Matrícula</label>
        <input type="text" name="registration_number" id="registration_number" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" value="{{ old('registration_number', $user->registration_number ?? '') }}" required>
    </div>


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
                        @php
                            $isChecked = in_array($permission->id, $directPermissions) || in_array($permission->id, $inheritedPermissions);
                            $isInherited = !in_array($permission->id, $directPermissions) && in_array($permission->id, $inheritedPermissions);
                            $isDenied = in_array($permission->name, old('denied_permissions', $user->denied_permissions ?? []));
                        @endphp

                        <div class="flex items-center space-x-2 group">
                            <label class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    class="permission-checkbox"
                                    data-permission-id="{{ $permission->id }}"
                                    data-modules="{{ $permission->module }}"
                                    {{ $isChecked ? 'checked' : '' }}
                                >
                                <span>
            {{ $permission->name }}
                                    @if($isInherited)
                                        <span class="text-xs italic text-gray-400">(herdada)</span>
                                    @endif
        </span>
                            </label>

                            @if($isInherited)
                                <button
                                    type="button"
                                    class="ml-1 text-xs text-red-500 hover:text-red-700 toggle-block"
                                    data-id="{{ $permission->id }}"
                                    title="Bloquear esta permissão herdada"
                                >
                                    @if($isDenied)
                                        <i class="fas fa-ban"></i> bloqueada
                                    @else
                                        <i class="far fa-circle"></i> bloquear
                                    @endif
                                </button>
                                <input type="hidden" name="denied_permissions[]" value="{{ $permission->name }}" class="hidden-block" data-id="{{ $permission->id }}" {{ $isDenied ? '' : 'disabled' }}>
                            @endif
                        </div>
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

        document.querySelectorAll('.toggle-block').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const input = document.querySelector(`input.hidden-block[data-id="${id}"]`);

                const isBlocked = !input.disabled;
                input.disabled = isBlocked;

                // Atualiza o ícone/texto visual
                if (isBlocked) {
                    this.innerHTML = '<i class="far fa-circle"></i> bloquear';
                } else {
                    this.innerHTML = '<i class="fas fa-ban"></i> bloqueada'
                }
            });
        });
    });
</script>
