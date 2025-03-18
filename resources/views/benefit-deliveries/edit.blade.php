<x-app-layout>
    <div class="md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500 my-2 ml-2" aria-label="Breadcrumb">
                <ol class="list-reset flex">
                    <li><a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Dashboard</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('benefit-deliveries.index') }}" class="text-indigo-600 hover:text-indigo-800">Gestão de Benefícios</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li>Editar</li>
                </ol>
            </nav>

            <div class="bg-white md:shadow-md md:rounded-md p-6">
                <h2 class="text-xl font-semibold mb-4">Editar entrega de benefício</h2>
                @include('benefit-deliveries.form', ['action' => route('benefit-deliveries.update', $benefitDelivery->id), 'method' => 'PUT', 'benefit_delivery' => $benefitDelivery])
            </div>
        </div>
    </div>
</x-app-layout>
