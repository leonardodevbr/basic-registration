@if($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="person-register-form" action="{{ $action }}" class="grid grid-cols-2 gap-4" method="POST">
    @csrf
    @method($method)
    <div class="flex flex-col">
        <label class="block">Selfie:</label>

        <!-- Aqui verificamos se existe uma selfie salva ou capturada -->
        @php
            $selfieValue = old('selfie', $person->selfie_url ?? null);
        @endphp

        @if($selfieValue)
            <img id="selfie-preview" class="border rounded w-full mt-2" src="{{ $selfieValue }}">
            <video id="video" class="border rounded w-full" autoplay style="display: none;"></video>
        @else
            <video id="video" class="border rounded w-full" autoplay></video>
            <img id="selfie-preview" class="border rounded w-full mt-2" style="display: none;">
        @endif

        <canvas id="canvas" class="border rounded w-full" style="display: none;"></canvas>
        <input type="hidden" name="selfie" id="selfie" value="{{ $selfieValue }}">

        <div class="flex mt-2 space-x-2">
            <button type="button" id="flip-btn" class="px-4 py-2 bg-gray-500 text-white rounded">Inverter</button>
            <button type="button" id="capture-btn" class="px-4 py-2 bg-blue-500 text-white rounded">
                {{ $selfieValue ? 'Capturar Novamente' : 'Tirar Selfie' }}
            </button>
            <button type="button" id="cancel-btn" class="px-4 py-2 bg-red-500 text-white rounded" style="display: {{ $selfieValue ? 'block' : 'none' }};">Cancelar</button>
        </div>
    </div>

    <div class="flex flex-col">
        <div class="mb-4">
            <label class="block">Nome:</label>
            <input type="text" name="name" class="border rounded w-full" value="{{ old('name', $person->name ?? '') }}">
        </div>

        <div class="mb-4">
            <label class="block">CPF:</label>
            <input type="text" name="cpf" class="border rounded w-full" value="{{ old('cpf', $person->cpf ?? '') }}">
        </div>

        <div class="mb-4">
            <label class="block">Telefone:</label>
            <input type="text" name="phone" class="border rounded w-full"
                   value="{{ old('phone', $person->phone ?? '') }}">
        </div>
        <button class="px-4 self-end py-2 bg-indigo-500 text-white rounded">Salvar</button>
    </div>
</form>
