<!-- resources/views/people/index.blade.php -->
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
                <ol class="list-reset flex">
                    <li><a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Dashboard</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li>Benefícios entregues</li>
                </ol>
            </nav>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Lista de Benefícios Entregues</h2>
                    <a href="{{ route('benefit-deliveries.create') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md">Novo registro</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto md:table-fixed divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left">Nome</th>
                            <th class="py-3 px-6 text-left">CPF</th>
                            <th class="py-3 px-6 text-left">Telefone</th>
                            <th class="py-3 px-6 text-left">Benefício</th>
                            <th class="py-3 px-6 text-left">Selfie</th>
                            <th class="py-3 px-6 text-left">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($benefitDeliveries as $benefitDelivery)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-4 px-6 whitespace-nowrap max-w-[150px] truncate">
                                    {{ $benefitDelivery->person->name }}
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    {{ preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $benefitDelivery->person->cpf) }}
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    {{ preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", $benefitDelivery->person->phone) }}
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap max-w-[150px] truncate">
                                    {{ $benefitDelivery->benefit->name }}
                                </td>
                                <td class="py-4 pr-6 text-center cursor-pointer">
                                    @if($benefitDelivery->person->selfie_path)
                                        <img src="{{ $benefitDelivery->person->thumb_url }}"
                                             alt="Selfie"
                                             loading="lazy"
                                             class="w-16 h-16 rounded-full object-cover mx-auto"
                                             onclick="openModal('{{ $benefitDelivery->id }}')">
                                    @else
                                        <span class="text-gray-500">Sem selfie</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('benefit-deliveries.edit', $benefitDelivery) }}"
                                           class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        <form action="{{ route('benefit-deliveries.destroy', $benefitDelivery) }}"
                                              method="POST" class="inline-block delete-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $benefitDeliveries->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="imageModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg relative">
            <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500">&times;</button>
            <h2 id="modalTitle" class="text-xl font-bold"></h2>
            <p id="modalCpf" class="text-sm text-gray-500"></p>
            <p id="modalPhone" class="text-sm text-gray-500"></p>
            <p id="modalBenefit" class="text-sm text-gray-500 mb-4"></p>
            <img id="modalImage" src="" class="w-80 h-80 rounded">
        </div>
    </div>
    @push('scripts')
        <script>
            function openModal(benefitDeliveryId) {
                const modalImage = document.getElementById("modalImage");
                const placeholder = "/placeholder.jpg";
                const loadingOverlay = document.getElementById("loading-overlay");

                // Exibe o loading na página
                loadingOverlay.classList.remove("hidden");

                // Define o placeholder antes de carregar a imagem real
                modalImage.src = placeholder;

                fetch(`/benefit-deliveries/${benefitDeliveryId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Pre-carrega a imagem antes de substituir
                        const img = new Image();
                        img.src = data.person.selfie_url;

                        img.onload = function () {
                            modalImage.src = data.person.selfie_url;
                        };

                        img.onerror = function () {
                            modalImage.src = placeholder;
                        };

                        document.getElementById("modalTitle").innerText = data.person.name;
                        document.getElementById("modalCpf").innerText = "CPF: " + data.person.cpf;
                        document.getElementById("modalPhone").innerText = "Telefone: " + data.person.phone;
                        document.getElementById("modalBenefit").innerText = "Benefício: " + data.benefit.name;
                        document.getElementById("imageModal").classList.remove("hidden");
                    })
                    .catch(error => {
                        console.error("Erro ao buscar selfie:", error);
                        alert("Erro ao carregar a imagem. Tente novamente.");
                        modalImage.src = placeholder;
                    })
                    .finally(() => {
                        // Esconde o loading quando os dados estiverem carregados
                        loadingOverlay.classList.add("hidden");
                    });
            }


            function closeModal() {
                document.getElementById("imageModal").classList.add("hidden");
            }


            function closeModal() {
                document.getElementById("imageModal").classList.add("hidden");
            }
        </script>
    @endpush
</x-app-layout>
