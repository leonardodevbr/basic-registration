<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 mb-4">
                <ol class="flex">
                    <li>Dashboard</li>
                </ol>
            </nav>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Dashboard</h2>
                <a href="{{ route('people.index') }}" class="px-4 py-2 bg-indigo-500 text-white rounded">Ver Lista de Pessoas</a>

                <div class="mt-6">
                    <!-- Aqui futuramente vão os gráficos -->
                    <p class="text-gray-600">Gráficos estarão disponíveis em breve.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
