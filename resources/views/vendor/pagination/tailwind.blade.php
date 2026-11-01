@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center gap-1">
        @php $base = 'grid h-9 min-w-9 place-items-center rounded-xl px-2.5 text-sm font-semibold transition'; @endphp

        @if ($paginator->onFirstPage())
            <span class="{{ $base }} text-ink-300"><span class="icon-[tabler--chevron-left] size-4"></span></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous"
                class="{{ $base }} text-ink-300 hover:bg-white/10"><span class="icon-[tabler--chevron-left] size-4"></span></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="{{ $base }} text-ink-400">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="{{ $base }} bg-brand-600 text-snow shadow-lg shadow-brand-600/30">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="{{ $base }} text-ink-300 hover:bg-white/10">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next"
                class="{{ $base }} text-ink-300 hover:bg-white/10"><span class="icon-[tabler--chevron-right] size-4"></span></a>
        @else
            <span class="{{ $base }} text-ink-300"><span class="icon-[tabler--chevron-right] size-4"></span></span>
        @endif
    </nav>
@endif
