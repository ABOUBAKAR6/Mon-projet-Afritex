{{-- Pagination AfriTex : cases carrées, page active en brun, cibles de 44px --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
        <p class="text-[0.92rem] text-encre-2">
            @if ($paginator->firstItem())
                <span class="chiffres">{{ $paginator->firstItem() }}</span> à <span class="chiffres">{{ $paginator->lastItem() }}</span>
                sur <span class="chiffres">{{ $paginator->total() }}</span>
            @else
                {{ $paginator->count() }} résultat(s)
            @endif
        </p>

        <ul class="flex flex-wrap items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="flex size-11 items-center justify-center text-encre-3"><x-icone nom="chevron-gauche" class="size-4" /><span class="sr-only">Page précédente</span></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex size-11 items-center justify-center bg-carte text-encre transition-colors hover:bg-brun-doux hover:text-brun-fonce"><x-icone nom="chevron-gauche" class="size-4" /><span class="sr-only">Page précédente</span></a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="flex size-11 items-center justify-center text-encre-2">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="chiffres flex size-11 items-center justify-center bg-brun font-semibold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="chiffres flex size-11 items-center justify-center bg-carte text-encre transition-colors hover:bg-brun-doux hover:text-brun-fonce" aria-label="Page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex size-11 items-center justify-center bg-carte text-encre transition-colors hover:bg-brun-doux hover:text-brun-fonce"><x-icone nom="chevron-droite" class="size-4" /><span class="sr-only">Page suivante</span></a>
                @else
                    <span aria-disabled="true" class="flex size-11 items-center justify-center text-encre-3"><x-icone nom="chevron-droite" class="size-4" /><span class="sr-only">Page suivante</span></span>
                @endif
            </li>
        </ul>
    </nav>
@endif
