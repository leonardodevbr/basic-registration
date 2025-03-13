<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-md p-6">
                <h2 class="text-xl font-semibold mb-4">Editar entrega de benefício</h2>
                @include('benefit-deliveries.form', ['action' => route('benefit-deliveries.update', $benefitDelivery->id), 'method' => 'PUT', 'benefit_delivery' => $benefitDelivery])
            </div>
        </div>
    </div>
</x-app-layout>
