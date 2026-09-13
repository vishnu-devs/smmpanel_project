<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;

class BlogController extends Controller
{
    /**
     * Display a listing of published blog posts.
     */
    public function index(Request $request)
    {
        $query = Blog::where('status', 'published');

        // Category filter
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $blogs = $query->latest()->paginate(9)->withQueryString();

        // Get distinct categories
        $categories = Blog::where('status', 'published')
            ->select('category')
            ->distinct()
            ->pluck('category');

        // Recent posts for sidebar / highlight
        $recentPosts = Blog::where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        return view('blog.index', compact('blogs', 'categories', 'recentPosts'));
    }

    /**
     * Display a single blog article by slug.
     */
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment view count
        $blog->increment('views');

        // Related posts in same category or latest
        $relatedPosts = Blog::where('status', 'published')
            ->where('id', '!=', $blog->id)
            ->where('category', $blog->category)
            ->latest()
            ->take(3)
            ->get();

        if ($relatedPosts->isEmpty()) {
            $relatedPosts = Blog::where('status', 'published')
                ->where('id', '!=', $blog->id)
                ->latest()
                ->take(3)
                ->get();
        }

        return view('blog.show', compact('blog', 'relatedPosts'));
    }
}
