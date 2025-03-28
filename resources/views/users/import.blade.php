@section('title', 'Importar Usuários')
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importar Usuários') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                @if(session('success'))
                    <div class="mb-4 text-green-600">{{ session('success') }}</div>
                @endif

                <form action="{{ route('users.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="file" class="block text-sm font-medium text-gray-700">Arquivo Excel</label>
                        <input type="file" name="file" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <x-primary-button>Importar</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
