@extends(Auth::check() ? 'layouts.app' : 'layouts.landing')

@section('title', 'Social Media Marketing & Growth Blog - ' . App\Models\Setting::get('site_name', 'RishiSMM'))
@section('page_header', 'Blog & Knowledge Hub')

@section('styles')
<style>
    .blog-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: {{ Auth::check() ? '0' : '2rem 5% 5rem 5%' }};
    }

    /* Hero Header */
    .blog-hero {
        text-align: center;
        padding: 3rem 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 3.5rem;
        background: radial-gradient(circle at 50% 20%, rgba(220, 39, 67, 0.15) 0%, transparent 70%),
                    var(--bg-card);
        border: 1px solid var(--border-color);
    }
    .blog-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: rgba(220, 39, 67, 0.1);
        border: 1px solid rgba(220, 39, 67, 0.3);
        border-radius: 30px;
        color: var(--color-primary);
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .blog-hero h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 12px;
    }
    .blog-hero p {
        color: var(--text-secondary);
        font-size: 1.05rem;
        max-width: 650px;
        margin: 0 auto 2rem auto;
    }

    /* Filter & Search Bar */
    .blog-filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 2.5rem;
    }
    .category-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .category-pill {
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        color: var(--text-secondary);
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }
    .category-pill:hover,
    .category-pill.active {
        background: var(--grad-insta);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(220, 39, 67, 0.25);
    }

    .blog-search-box {
        position: relative;
        min-width: 260px;
    }
    .blog-search-box input {
        padding-left: 38px;
    }
    .blog-search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }

    /* Blog Grid */
    .blog-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }
    .blog-card {
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }
    .blog-card:hover {
        transform: translateY(-5px);
        border-color: rgba(220, 39, 67, 0.35);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }
    .blog-card-img-wrap {
        height: 200px;
        background: rgba(255, 255, 255, 0.02);
        overflow: hidden;
        position: relative;
    }
    .blog-card-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .blog-card:hover .blog-card-img-wrap img {
        transform: scale(1.05);
    }
    .blog-card-category-badge {
        position: absolute;
        top: 14px;
        left: 14px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .blog-card-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .blog-card-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 10px;
    }
    .blog-card-title {
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 10px;
        color: var(--text-primary);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .blog-card:hover .blog-card-title {
        color: var(--color-primary);
    }
    .blog-card-excerpt {
        font-size: 0.88rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 1.25rem;
        flex-grow: 1;
    }
    .blog-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        padding-top: 1rem;
        font-size: 0.85rem;
        font-weight: 600;
    }

    @media (max-width: 1024px) {
        .blog-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 768px) {
        .blog-hero h1 {
            font-size: 2rem;
        }
        .blog-grid {
            grid-template-columns: 1fr;
        }
        .blog-filter-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .blog-search-box {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="blog-container animate-fade-in">

    <!-- Hero Header -->
    <div class="glass blog-hero">
        <div class="blog-badge">
            <i class="fa-solid fa-newspaper"></i> Articles & Guides
        </div>
        <h1>Social Media Marketing <span class="text-gradient">Blog & Insights</span></h1>
        <p>Master Instagram growth, YouTube monetization, SMM reseller strategies, and digital marketing trends.</p>

        <!-- Search Form -->
        <form action="{{ route('blog.index') }}" method="GET" style="max-width: 500px; margin: 0 auto;">
            <div class="search-wrapper" style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search articles, guides, topics..." style="padding-left: 45px; border-radius: 30px; height: 48px;">
            </div>
        </form>
    </div>

    <!-- Category Filter Bar -->
    <div class="blog-filter-bar">
        <div class="category-pills">
            <a href="{{ route('blog.index') }}" class="category-pill {{ !request('category') || request('category') === 'all' ? 'active' : '' }}">
                🌐 All Articles
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('blog.index', ['category' => $cat]) }}" class="category-pill {{ request('category') === $cat ? 'active' : '' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
        <div style="font-size: 0.85rem; color: var(--text-secondary);">
            Showing <strong>{{ $blogs->total() }}</strong> articles
        </div>
    </div>

    <!-- Blog Posts Grid -->
    @if($blogs->count() > 0)
        <div class="blog-grid">
            @foreach($blogs as $blog)
                <article class="glass blog-card">
                    <div class="blog-card-img-wrap">
                        @if($blog->image)
                            <img src="{{ asset($blog->image) }}" alt="{{ $blog->title }}">
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(220,39,67,0.2), rgba(124,58,237,0.2)); color: var(--color-primary); font-size: 2.5rem;">
                                <i class="fa-solid fa-newspaper"></i>
                            </div>
                        @endif
                        <span class="blog-card-category-badge">{{ $blog->category }}</span>
                    </div>

                    <div class="blog-card-body">
                        <div class="blog-card-meta">
                            <span><i class="fa-regular fa-calendar" style="margin-right: 4px;"></i> {{ $blog->created_at->format('M d, Y') }}</span>
                            <span>•</span>
                            <span><i class="fa-regular fa-clock" style="margin-right: 4px;"></i> {{ $blog->reading_time }} min read</span>
                        </div>

                        <a href="{{ route('blog.show', $blog->slug) }}" class="blog-card-title">
                            {{ $blog->title }}
                        </a>

                        <p class="blog-card-excerpt">
                            {{ $blog->excerpt }}
                        </p>

                        <div class="blog-card-footer">
                            <span style="color: var(--text-muted);"><i class="fa-regular fa-eye" style="margin-right: 4px;"></i> {{ number_format($blog->views) }} views</span>
                            <a href="{{ route('blog.show', $blog->slug) }}" style="color: var(--color-primary); text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                Read More <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div style="margin-top: 3rem;">
            {{ $blogs->links() }}
        </div>
    @else
        <div class="glass" style="text-align: center; padding: 4rem 2rem; border-radius: var(--radius-md);">
            <i class="fa-solid fa-magnifying-glass" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h3 style="font-size: 1.3rem; margin-bottom: 8px;">No Blog Articles Found</h3>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">No posts matched your search criteria.</p>
            <a href="{{ route('blog.index') }}" class="btn-gradient" style="padding: 10px 24px; text-decoration: none; border-radius: var(--radius-md);">
                View All Articles
            </a>
        </div>
    @endif

</div>
@endsection
