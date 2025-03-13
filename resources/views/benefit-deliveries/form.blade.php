@if($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="w-full mx-auto bg-white rounded-lg">
    <form id="benefit-delivery-register-form" action="{{ $action }}" class="grid grid-cols-1 md:grid-cols-2 gap-4"
          method="POST">
        @csrf
        @method($method)
        <div class="flex flex-col">
            <div class="mb-4">
                <label class="block">CPF:</label>
                <input type="text" name="person[cpf]" class="border rounded w-full p-2"
                       value="{{ old('person.cpf', $benefitDelivery->person->cpf ?? '') }}">
            </div>

            <div class="mb-4">
                <label class="block">Selfie:</label>

                @php
                    $selfieValue = old('person.selfie', $benefitDelivery->person->selfie_url ?? null);
                @endphp

                <div id="error-message" class="text-red-500 mt-2 hidden"></div>

                <div class="video-container {{ $selfieValue ? 'hidden' : '' }}">
                    <video id="video" class="border rounded w-full" autoplay></video>
                    <canvas id="canvas"></canvas>
                </div>

                <img id="selfie-preview" class="border rounded w-full mt-2 {{ $selfieValue ? '' : 'hidden' }}"
                     src="{{ $selfieValue }}" alt="Selfie">

                <input type="hidden" name="person[selfie]" id="selfie" value="{{ $selfieValue }}">

                <div class="flex mt-2 space-x-2">
                    <button type="button" id="flip-btn" class="px-4 py-2 bg-gray-500 text-white rounded w-full">
                        Inverter
                    </button>
                    <button type="button" id="capture-btn" class="px-4 py-2 bg-blue-500 text-white rounded w-full">
                        {{ $selfieValue ? 'Capturar Novamente' : 'Tirar Selfie' }}
                    </button>
                    <button type="button" id="cancel-btn"
                            class="px-4 py-2 bg-red-500 text-white rounded w-full {{ $selfieValue ? '' : 'hidden' }}">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <div class="mb-4">
                <label class="block">Nome:</label>
                <input type="text" name="person[name]" class="border rounded w-full p-2"
                       value="{{ old('person.name', $benefitDelivery->person->name ?? '') }}">
            </div>

            <div class="mb-4">
                <label class="block">Telefone:</label>
                <input type="text" name="person[phone]" class="border rounded w-full p-2"
                       value="{{ old('person.phone', $benefitDelivery->person->phone ?? '') }}">
            </div>

            <div class="mb-4">
                <label class="block">Benefício:</label>
                <select name="benefit_id" class="border rounded w-full p-2">
                    @foreach($benefits as $benefit)
                        <option
                            value="{{ $benefit->id }}" {{ old('benefit_id', $benefitDelivery->benefit_id ?? '') == $benefit->id ? 'selected' : '' }}>
                            {{ $benefit->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="w-full px-4 py-2 bg-indigo-500 text-white rounded">Salvar</button>
            <a class="w-full px-4 py-2 bg-gray-500 text-white rounded text-center mt-2"
               href="{{route('benefit-deliveries.index')}}">Voltar</a>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("benefit-delivery-register-form");
            const loadingOverlay = document.getElementById("loading-overlay");

            if (form) {
                form.addEventListener("submit", function () {
                    loadingOverlay.classList.remove("hidden"); // Exibe o loading
                });
            }
        });
    </script>
@endpush
