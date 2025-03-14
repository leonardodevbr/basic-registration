@foreach($deliveries as $benefitDelivery)
    <tr class="border-b hover:bg-gray-50" data-code="{{ $benefitDelivery->password_code }}">
        <td class="py-4 md:px-6 cursor-pointer">
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
            <span>{{ $benefitDelivery->benefit->name }}</span><br>
            <div class="status-col">
                @switch($benefitDelivery->status)
                    @case('PENDING')
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Pendente</span>
                        @break
                    @case('DELIVERED')
                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Entregue</span>
                        @break
                    @case('EXPIRED')
                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Expirado</span>
                        @break
                @endswitch
            </div>
        </td>
        <td class="py-4 px-6 whitespace-nowrap max-w-[150px] truncate">
            {{ $benefitDelivery->benefit->created_at->format('d/m/Y H:i') }}
        </td>
        <td class="py-4 px-6">
            <div class="flex items-center space-x-3">
                <a href="{{ route('benefit-deliveries.edit', $benefitDelivery) }}"
                   class="bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600">
                    Editar
                </a>
                <form action="{{ route('benefit-deliveries.destroy', $benefitDelivery) }}"
                      method="POST" class="inline-block delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                        Excluir
                    </button>
                </form>
                @if($benefitDelivery->status === 'PENDING')
                    <button type="button"
                            class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600"
                            onclick="confirmDelivery({{ $benefitDelivery->id }})">
                        Dar Baixa
                    </button>
                @endif
            </div>
        </td>
    </tr>
@endforeach
