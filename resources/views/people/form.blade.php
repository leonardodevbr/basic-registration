@if($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" class="grid grid-cols-2 gap-4" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif
    <div class="flex flex-col">
        <label class="block">Selfie:</label>
        <video id="video" class="border rounded w-full" autoplay></video>
        <canvas id="canvas" class="border rounded w-full"></canvas>
        <input type="hidden" name="selfie" id="selfie">
        <button type="button" id="capture-btn" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded">
            Tirar Selfie
        </button>
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
