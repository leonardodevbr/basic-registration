{{--resources/views/access-control/permissions/edit.blade.php--}}
@section('title', 'Editar Permissão')
<x-app-layout>
    <div class="md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-500 my-2 ml-2" aria-label="Breadcrumb">
                <ol class="list-reset flex">
                    <li><a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Dashboard</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('access-control.permissions') }}" class="text-indigo-600 hover:text-indigo-800">Controle de Acesso</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li>Editar Permissão</li>
                </ol>
            </nav>

            <div class="bg-white md:shadow-md md:rounded-md p-6">
                <h2 class="text-xl font-semibold mb-4">Editar Permissão</h2>
                @include('access-control.permissions.form', [
                    'action' => route('permissions.update', $permission->id),
                    'method' => 'PUT',
                    'permission' => $permission
                ])
            </div>
        </div>
    </div>
</x-app-layout>
