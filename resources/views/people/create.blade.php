<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-xl mb-4">Cadastrar Pessoa</h2>
                @include('people.form', ['action' => route('people.store'), 'method' => 'POST'])
            </div>
        </div>
    </div>
</x-app-layout>
