@extends(Auth::check() ? 'layouts.app' : 'layouts.landing')

@section('title', ($blog->meta_title ?: $blog->title) . ' - ' . App\Models\Setting::get('site_name', 'RishiSMM'))
@section('page_header', $blog->title)

@section('styles')
    <style>
        .article-container {
            max-width: 850px;
            margin: 0 auto;
            padding:
                {{ Auth::check() ? '0' : '2rem 5% 5rem 5%' }}
            ;
        }

        .article-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .article-category-badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 30px;
            background: rgba(220, 39, 67, 0.12);
            border: 1px solid rgba(220, 39, 67, 0.35);
            color: var(--color-primary);
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .article-title {
            font-size: 2.6rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.5px;
            margin-bottom: 1.25rem;
        }

        .article-meta-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 0.9rem;
            color: var(--text-secondary);
            flex-wrap: wrap;
        }

        .article-featured-img {
            width: 100%;
            max-height: 440px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 2.5rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
        }

        .article-featured-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .article-body {
            font-size: 1.05rem;
            line-height: 1.85;
            color: var(--text-secondary);
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            margin-bottom: 3.5rem;
        }

        .article-body h2,
        .article-body h3,
        .article-body h4 {
            color: var(--text-primary);
            font-weight: 800;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .article-body h2 {
            font-size: 1.6rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }

        .article-body h3 {
            font-size: 1.3rem;
        }

        .article-body p {
            margin-bottom: 1.25rem;
        }

        .article-body ul,
        .article-body ol {
            margin-bottom: 1.5rem;
            padding-left: 24px;
        }

        .article-body li {
            margin-bottom: 8px;
        }

        .article-body blockquote {
            border-left: 4px solid var(--color-primary);
            padding: 1rem 1.5rem;
            background: rgba(220, 39, 67, 0.05);
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
            margin: 1.5rem 0;
            font-style: italic;
            color: var(--text-primary);
        }

        /* Social Share Bar */
        .share-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
            margin-top: 2.5rem;
        }

        .share-btn {
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: opacity 0.2s ease;
        }

        .share-btn:hover {
            opacity: 0.9;
        }

        /* Related Posts Grid */
        .related-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .article-title {
                font-size: 1.9rem;
            }

            .article-body {
                padding: 1.5rem;
                font-size: 0.98rem;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="article-container animate-fade-in">

        <!-- Breadcrumbs / Back Link -->
        <div style="margin-bottom: 1.5rem;">
            <a href="{{ route('blog.index') }}"
                style="color: var(--color-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Back to All Articles
            </a>
        </div>

        <!-- Article Header -->
        <header class="article-header">
            <span class="article-category-badge">{{ $blog->category }}</span>
            <h1 class="article-title">{{ $blog->title }}</h1>
            <div class="article-meta-bar">
                <span><i class="fa-solid fa-user-pen" style="margin-right: 5px;"></i> By {{ $blog->author }}</span>
                <span>•</span>
                <span><i class="fa-regular fa-calendar" style="margin-right: 5px;"></i>
                    {{ $blog->created_at->format('F d, Y') }}</span>
                <span>•</span>
                <span><i class="fa-regular fa-clock" style="margin-right: 5px;"></i> {{ $blog->reading_time }} min
                    read</span>
                <span>•</span>
                <span><i class="fa-regular fa-eye" style="margin-right: 5px;"></i> {{ number_format($blog->views) }}
                    views</span>
            </div>
        </header>

        <!-- Featured Image -->
        @if($blog->image)
            <div class="article-featured-img">
                <img src="{{ asset($blog->image) }}" alt="{{ $blog->title }}">
            </div>
        @endif

        <!-- Article Body Content -->
        <article class="glass article-body">
            {!! $blog->content !!}

            <!-- Social Share Bar -->
            <div class="share-bar">
                <strong style="color: var(--text-primary); font-size: 0.9rem;">Share Article:</strong>
                @php
                    $shareUrl = urlencode(url()->current());
                    $shareTitle = urlencode($blog->title);
                @endphp

                <button type="button"
                    onclick="navigator.clipboard.writeText(window.location.href); this.innerHTML = '<i class=\'fa-solid fa-check\'></i> Copied!'; setTimeout(() => this.innerHTML = '<i class=\'fa-regular fa-copy\'></i> Copy Link', 2000);"
                    class="share-btn"
                    style="background: rgba(255,255,255,0.08); border: 1px solid var(--border-color); color: var(--text-primary); cursor: pointer;">
                    <i class="fa-regular fa-copy"></i> Copy Link
                </button>
            </div>
        </article>

        <!-- Related Articles Section -->
        @if($relatedPosts->count() > 0)
            <div style="margin-top: 4rem;">
                <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem;">
                    Related <span class="text-gradient">Articles</span>
                </h3>
                <div class="related-grid">
                    @foreach($relatedPosts as $rel)
                        <div class="glass"
                            style="border-radius: var(--radius-md); border: 1px solid var(--border-color); overflow: hidden; display: flex; flex-direction: column;">
                            @if($rel->image)
                                <div style="height: 140px; overflow: hidden;">
                                    <img src="{{ asset($rel->image) }}" alt="{{ $rel->title }}"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            @endif
                            <div style="padding: 1.25rem; flex-grow: 1; display: flex; flex-direction: column;">
                                <span
                                    style="font-size: 0.75rem; color: var(--color-primary); font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">{{ $rel->category }}</span>
                                <a href="{{ route('blog.show', $rel->slug) }}"
                                    style="font-size: 1rem; font-weight: 700; color: var(--text-primary); text-decoration: none; line-height: 1.4; margin-bottom: 8px;">
                                    {{ \Illuminate\Support\Str::limit($rel->title, 55) }}
                                </a>
                                <span
                                    style="font-size: 0.8rem; color: var(--text-muted); margin-top: auto;">{{ $rel->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
@endsection