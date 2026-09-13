@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        
        // Dynamic compact 3-page window
        $start = max(1, $current - 1);
        $end = min($last, $current + 1);

        if ($current <= 2) {
            $start = 1;
            $end = min(3, $last);
        }
        if ($current >= $last - 1) {
            $start = max(1, $last - 2);
            $end = $last;
        }
    @endphp

    <div style="display: flex; flex-direction: column; align-items: center; gap: 10px; margin-top: 1.5rem; width: 100%; box-sizing: border-box;">
        <div style="font-size: 0.82rem; color: var(--text-muted); text-align: center;">
            Showing <span style="font-weight: 700; color: var(--text-primary);">{{ $paginator->firstItem() }}</span> to <span style="font-weight: 700; color: var(--text-primary);">{{ $paginator->lastItem() }}</span> of <span style="font-weight: 700; color: var(--text-primary);">{{ number_format($paginator->total()) }}</span> results
        </div>

        <div style="display: flex; gap: 6px; align-items: center; justify-content: center; flex-wrap: wrap;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span style="padding: 6px 12px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.03); color: var(--text-muted); font-size: 0.85rem; cursor: not-allowed; opacity: 0.5;">
                    &laquo; Prev
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" style="padding: 6px 12px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.08); color: var(--text-primary); text-decoration: none; font-size: 0.85rem; border: 1px solid var(--border-color); transition: var(--transition);">
                    &laquo; Prev
                </a>
            @endif

            {{-- First page if outside window --}}
            @if ($start > 1)
                <a href="{{ $paginator->url(1) }}" style="padding: 6px 12px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.08); color: var(--text-primary); text-decoration: none; font-size: 0.85rem; border: 1px solid var(--border-color); transition: var(--transition);">1</a>
                @if ($start > 2)
                    <span style="padding: 6px 4px; color: var(--text-muted); font-size: 0.85rem;">...</span>
                @endif
            @endif

            {{-- Numbered Page Window (e.g. 1, 2, 3 OR 3, 4, 5) --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $current)
                    <span style="padding: 6px 12px; border-radius: var(--radius-sm); background: var(--grad-insta); color: #ffffff !important; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(220,39,67,0.35);">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $paginator->url($page) }}" style="padding: 6px 12px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.08); color: var(--text-primary); text-decoration: none; font-size: 0.85rem; border: 1px solid var(--border-color); transition: var(--transition);">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            {{-- Last page if outside window --}}
            @if ($end < $last)
                @if ($end < $last - 1)
                    <span style="padding: 6px 4px; color: var(--text-muted); font-size: 0.85rem;">...</span>
                @endif
                <a href="{{ $paginator->url($last) }}" style="padding: 6px 12px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.08); color: var(--text-primary); text-decoration: none; font-size: 0.85rem; border: 1px solid var(--border-color); transition: var(--transition);">{{ $last }}</a>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" style="padding: 6px 12px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.08); color: var(--text-primary); text-decoration: none; font-size: 0.85rem; border: 1px solid var(--border-color); transition: var(--transition);">
                    Next &raquo;
                </a>
            @else
                <span style="padding: 6px 12px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.03); color: var(--text-muted); font-size: 0.85rem; cursor: not-allowed; opacity: 0.5;">
                    Next &raquo;
                </span>
            @endif
        </div>
    </div>
@endif
