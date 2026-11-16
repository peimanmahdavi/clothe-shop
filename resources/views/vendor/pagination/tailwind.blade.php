@if ($paginator->hasPages())
    <nav role="navigation" aria-label="ناوبری صفحه‌بندی" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-[11px] text-muted">
            نمایش
            <span class="font-bold text-ink">{{ $paginator->firstItem() }}</span>
            تا
            <span class="font-bold text-ink">{{ $paginator->lastItem() }}</span>
            از
            <span class="font-bold text-ink">{{ $paginator->total() }}</span>
            مورد
        </p>

        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-lg border border-line bg-canvas px-3 py-1.5 text-xs font-bold text-muted/60">« قبلی</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-bold text-ink transition hover:border-brand hover:text-brand-deep">« قبلی</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center px-2 text-xs text-muted">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex min-w-8 items-center justify-center rounded-lg bg-brand-strong px-2.5 py-1.5 text-xs font-extrabold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex min-w-8 items-center justify-center rounded-lg border border-line bg-white px-2.5 py-1.5 text-xs font-bold text-ink transition hover:border-brand hover:text-brand-deep">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-bold text-ink transition hover:border-brand hover:text-brand-deep">بعدی »</a>
            @else
                <span class="inline-flex items-center rounded-lg border border-line bg-canvas px-3 py-1.5 text-xs font-bold text-muted/60">بعدی »</span>
            @endif
        </div>
    </nav>
@endif
