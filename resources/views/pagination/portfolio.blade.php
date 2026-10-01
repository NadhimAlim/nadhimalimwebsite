@if ($paginator->hasPages())
    <nav class="portfolio-pagination" aria-label="Navigasi halaman portofolio">
        @if ($paginator->onFirstPage())
            <span class="page-arrow disabled" aria-disabled="true"><i class="bi bi-arrow-left"></i><span>Sebelumnya</span></span>
        @else
            <a class="page-arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-arrow-left"></i><span>Sebelumnya</span></a>
        @endif

        <div class="page-numbers">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page-dots">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-number current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="page-number" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a class="page-arrow" href="{{ $paginator->nextPageUrl() }}" rel="next"><span>Berikutnya</span><i class="bi bi-arrow-right"></i></a>
        @else
            <span class="page-arrow disabled" aria-disabled="true"><span>Berikutnya</span><i class="bi bi-arrow-right"></i></span>
        @endif
    </nav>
@endif
