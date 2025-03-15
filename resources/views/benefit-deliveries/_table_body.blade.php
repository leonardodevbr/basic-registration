@foreach($deliveries as $benefitDelivery)
    <tr class="border-b hover:bg-gray-50" data-code="{{ $benefitDelivery->ticket_code }}">
        <td class="py-4 pr-2 md:px-6 whitespace-nowrap cursor-pointer">
            <div class="flex min-w-max items-center space-x-4" onclick="openModal('{{ $benefitDelivery->id }}')">
                @if($benefitDelivery->person->selfie_path)
                    <img src="{{ $benefitDelivery->person->thumb_url }}"
                         alt="Selfie"
                         loading="lazy"
                         class="w-16 h-16 rounded-full object-cover shadow">
                @else
                    <img src="{{ asset('placeholder.png')}}"
                         alt="Selfie"
                         loading="lazy"
                         class="w-16 h-16 rounded-full object-cover shadow">
                @endif

                <div>
                    <p class="text-gray-900 font-semibold">{{ str()->words($benefitDelivery->person->name, 2, '') }}</p>
                    <p class="text-gray-600 text-sm">
                        CPF: {{ preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $benefitDelivery->person->cpf) }}
                    </p>
                    @if(!empty($benefitDelivery->person->phone))
                        <p class="text-gray-600 text-sm">
                            Telefone: {{ preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", $benefitDelivery->person->phone) }}
                        </p>
                    @endif
                </div>
            </div>
        </td>
        <td class="py-4 px-2 md:px-6 whitespace-nowrap hidden md:table-cell">
            <div class="min-w-max">
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
                        @case('REISSUED')
                            <span class="bg-gray-200 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Reemitido</span>
                            @break
                    @endswitch
                </div>
            </div>
        </td>
        <td class="py-4 px-2 md:px-6 whitespace-nowrap hidden md:table-cell">
            <div class="flex min-w-max items-center space-x-1 mt-1">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5.121 17.804A12.042 12.042 0 0112 15.5c2.225 0 4.312.606 6.121 1.664M12 12a4 4 0 100-8 4 4 0 000 8z"></path>
                </svg>
                <span class="text-sm text-gray-700">
                    {{ $benefitDelivery->registered_by ? $benefitDelivery->user->name : 'Não informado' }}
                </span>
            </div>
            <p class="text-sm text-gray-600 mt-1">
                {{ $benefitDelivery->created_at->format('d/m/Y H:i') }}
            </p>
        </td>
        <td class="py-4 px-2 md:px-6 whitespace-nowrap hidden md:table-cell">
            @if($benefitDelivery->delivered_at)
                <div class="flex min-w-max items-center space-x-1 mt-1">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5.121 17.804A12.042 12.042 0 0112 15.5c2.225 0 4.312.606 6.121 1.664M12 12a4 4 0 100-8 4 4 0 000 8z"></path>
                    </svg>
                    <span class="text-sm text-gray-700">
                        {{ $benefitDelivery->delivered_by ? $benefitDelivery->user->name : 'Não informado' }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $benefitDelivery->delivered_at->format('d/m/Y H:i') }}
                </p>
            @elseif($benefitDelivery->status === 'EXPIRED')
                <p class="text-sm text-red-500 italic">Expirado
                    em {{ $benefitDelivery->valid_until->format('d/m/Y H:i') }}</p>
            @else
                <p class="text-sm text-gray-500 italic">Pendente</p>
            @endif
        </td>
        <td class="py-4 pl-2 md:px-6 whitespace-nowrap">
            <div class="hidden md:flex min-w-max items-center space-x-3 justify-end">
                @if($benefitDelivery->status === 'PENDING')
                    <button type="button"
                            class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600"
                            onclick="confirmDelivery({{ $benefitDelivery->id }})">
                        Entregar
                    </button>
                    <a href="{{ route('benefit-deliveries.edit', $benefitDelivery) }}"
                       class="bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600">
                        Editar
                    </a>
                @elseif($benefitDelivery->status === 'EXPIRED')
                    <button id="reissue-btn-{{ $benefitDelivery->id }}"
                            type="button"
                            class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                            onclick="reissueTicket({{ $benefitDelivery->id }})"
                        {{ $benefitDelivery->status === 'REISSUED' ? 'disabled' : '' }}>
                        Reemitir
                    </button>
                @endif
                <form action="{{ route('benefit-deliveries.destroy', $benefitDelivery) }}"
                      method="POST" class="inline-block delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                        Excluir
                    </button>
                </form>
            </div>

            <!-- Dropdown para Mobile -->
            <div class="md:hidden justify-end">
                <button class="dropdown-button bg-gray-200 text-gray-700 px-2 py-1 rounded-md flex items-center ml-auto">
                    <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>

                <div class="hidden dropdown-actions absolute right-5 mt-2 bg-white shadow-lg border rounded-md w-32 z-10">
                    @if($benefitDelivery->status === 'PENDING')
                        <button type="button"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                onclick="confirmDelivery({{ $benefitDelivery->id }}); toggleDropdown(this.closest('.relative').querySelector('button'))">
                            Entregar
                        </button>
                        <a href="{{ route('benefit-deliveries.edit', $benefitDelivery) }}"
                           class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                           onclick="toggleDropdown(this.closest('.relative').querySelector('button'))">
                            Editar
                        </a>
                    @elseif($benefitDelivery->status === 'EXPIRED')
                        <button id="reissue-btn-{{ $benefitDelivery->id }}"
                                type="button"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                onclick="reissueTicket({{ $benefitDelivery->id }}); toggleDropdown(this.closest('.relative').querySelector('button'))"
                            {{ $benefitDelivery->status === 'REISSUED' ? 'disabled' : '' }}>
                            Reemitir
                        </button>
                    @endif
                    <form action="{{ route('benefit-deliveries.destroy', $benefitDelivery) }}"
                          method="POST" class="block w-full text-left delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                onclick="toggleDropdown(this.closest('.relative').querySelector('button'))">
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        </td>

    </tr>
@endforeach
