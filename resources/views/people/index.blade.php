<!-- resources/views/people/index.blade.php -->
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
                <ol class="list-reset flex">
                    <li><a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Dashboard</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li>Pessoas</li>
                </ol>
            </nav>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Lista de Pessoas</h2>
                    <a href="{{ route('people.create') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md">Adicionar Pessoa</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left">Nome</th>
                            <th class="py-3 px-6 text-left">CPF</th>
                            <th class="py-3 px-6 text-left">Telefone</th>
                            <th class="py-3 px-6 text-left">Selfie</th>
                            <th class="py-3 px-6 text-left">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($people as $person)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-4 px-6">{{ $person->name }}</td>
                                <td class="py-4 px-6">{{ preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $person->cpf) }}</td>
                                <td class="py-4 px-6">{{ preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", $person->phone) }}</td>
                                <td class="py-4 px-6">
                                    @if($person->selfie_path)
                                        <img src="{{ $person->thumb_url }}" alt="Selfie" class="w-16 h-16 rounded-full cursor-pointer"
                                             onclick="openModal('{{ $person->id }}')">
                                    @else
                                        <span class="text-gray-500">Sem selfie</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <a href="{{ route('people.edit', $person) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                    <form action="{{ route('people.destroy', $person) }}" method="POST" class="inline-block ml-3 delete-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $people->links() }}
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
            <p id="modalPhone" class="text-sm text-gray-500 mb-4"></p>
            <img id="modalImage" src="" class="w-80 h-80 rounded">
        </div>
    </div>
    @push('scripts')
        <script>
            function openModal(personId) {
                fetch(`/people/${personId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById("modalImage").src = data.selfie_url;
                        document.getElementById("modalTitle").innerText = data.name;
                        document.getElementById("modalCpf").innerText = "CPF: " + data.cpf;
                        document.getElementById("modalPhone").innerText = "Telefone: " + data.phone;
                        document.getElementById("imageModal").classList.remove("hidden");
                    })
                    .catch(error => {
                        console.error("Erro ao buscar selfie:", error);
                        alert("Erro ao carregar a imagem. Tente novamente.");
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
