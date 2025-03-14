@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginação" class="flex justify-center mt-4">
        <ul class="inline-flex space-x-1 items-baseline">
            {{-- Link "Anterior" --}}
            @if ($paginator->onFirstPage())
                <li class="px-3 py-2 bg-gray-300 text-gray-500 cursor-not-allowed rounded-md">
                    Anterior
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-200">
                        Anterior
                    </a>
                </li>
            @endif

            {{-- Links das páginas --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="px-3 py-2 text-gray-500">{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="px-3 py-2 bg-indigo-500 text-white rounded-md">
                                {{ $page }}
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-200">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Link "Próximo" --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-200">
                        Próximo
                    </a>
                </li>
            @else
                <li class="px-3 py-2 bg-gray-300 text-gray-500 cursor-not-allowed rounded-md">
                    Próximo
                </li>
            @endif
        </ul>
    </nav>
@endif
