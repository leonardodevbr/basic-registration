{{--resources/views/benefit-deliveries/create.blade.php--}}
@section('title', 'Novo Registro')
<x-app-layout>
    <div class="md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500 my-2 ml-2" aria-label="Breadcrumb">
                <ol class="list-reset flex">
                    <li><a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Dashboard</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('benefit-deliveries.index') }}" class="text-indigo-600 hover:text-indigo-800">Gestão de Benefícios</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li>Novo Registro</li>
                </ol>
            </nav>

            <div class="bg-white md:shadow-md md:rounded-md py-6">
                <div class="flex items-center justify-between mb-4 px-3">
                    <h2 class="text-xl">Novo Registro</h2>
                    <div id="searchPersonBtn" class="flex justify-end">
                        <button type="button" id="open-search-modal" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
                            </svg>
                            Pesquisar Pessoa
                        </button>
                    </div>
                </div>

                @include('benefit-deliveries.form', ['action' => route('benefit-deliveries.store'), 'method' => 'POST'])
            </div>
        </div>
    </div>
</x-app-layout>
