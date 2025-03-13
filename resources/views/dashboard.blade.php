<x-app-layout>
    <div class="md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white md:shadow-md md:rounded-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Dashboard</h2>
                <a href="{{ route('benefit-deliveries.index') }}" class="px-4 py-2 bg-indigo-500 text-white rounded">Ver registro de entregas</a>

                <div class="mt-6">
                    <!-- Aqui futuramente vão os gráficos -->
                    <p class="text-gray-600">Gráficos estarão disponíveis em breve.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
