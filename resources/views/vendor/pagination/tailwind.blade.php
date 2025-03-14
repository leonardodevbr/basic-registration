@if ($paginator->hasPages())
    {{-- Definir limite de botões para mobile --}}
    @php
        $isMobile = $agent->isMobile();
        $maxButtons = $isMobile ? 3 : 6; // Mobile exibe menos botões
        $currentPage = $paginator->currentPage();
        $totalPages = $paginator->lastPage();
        $startPage = max(2, $currentPage - ceil($maxButtons / 2)); // Começa a partir da 2ª página
        $endPage = min($totalPages - 1, $startPage + $maxButtons - 1); // Termina antes da última página
    @endphp

    <nav role="navigation" aria-label="Paginação" class="flex justify-center mt-4">
        <ul class="inline-flex space-x-1 items-baseline">
            {{-- Link "Anterior" --}}
            @if ($paginator->onFirstPage())
                <li class="px-3 py-2 bg-gray-300 text-gray-500 cursor-not-allowed rounded-md">
                    {{$isMobile ? "<<" : "Anterior"}}
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-200">
                        {{$isMobile ? "<<" : "Anterior"}}
                    </a>
                </li>
            @endif

            {{-- Sempre exibir a primeira página --}}
            @if ($currentPage == 1)
                <li class="px-3 py-2 bg-indigo-500 text-white rounded-md font-bold">
                    1
                </li>
            @else
                <li>
                    <a href="{{ $paginator->url(1) }}" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-200">
                        1
                    </a>
                </li>
            @endif

            {{-- Exibir "..." se houver páginas ocultas entre 1 e os intermediários --}}
            @if ($startPage > 2)
                <li class="px-3 py-2 text-gray-500">...</li>
            @endif

            {{-- Links das páginas intermediárias --}}
            @foreach (range($startPage, $endPage) as $page)
                @if ($page == $currentPage)
                    <li class="px-3 py-2 bg-indigo-500 text-white rounded-md font-bold">
                        {{ $page }}
                    </li>
                @else
                    <li>
                        <a href="{{ $paginator->url($page) }}" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-200">
                            {{ $page }}
                        </a>
                    </li>
                @endif
            @endforeach

            {{-- Exibir "..." se houver páginas ocultas antes da última --}}
            @if ($endPage < $totalPages - 1)
                <li class="px-3 py-2 text-gray-500">...</li>
            @endif

            {{-- Sempre exibir a última página --}}
            @if ($currentPage == $totalPages)
                <li class="px-3 py-2 bg-indigo-500 text-white rounded-md font-bold">
                    {{ $totalPages }}
                </li>
            @else
                <li>
                    <a href="{{ $paginator->url($totalPages) }}" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-200">
                        {{ $totalPages }}
                    </a>
                </li>
            @endif

            {{-- Link "Próximo" --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-200">
                        {{$isMobile ? ">>" : "Próximo"}}
                    </a>
                </li>
            @else
                <li class="px-3 py-2 bg-gray-300 text-gray-500 cursor-not-allowed rounded-md">
                    {{$isMobile ? ">>" : "Próximo"}}
                </li>
            @endif
        </ul>
    </nav>
@endif
