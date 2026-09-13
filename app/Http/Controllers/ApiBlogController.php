<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiBlogController extends Controller
{
    /**
     * API Endpoint for n8n / External Automation to create a blog post.
     * Endpoint: POST /api/v1/blog/create
     */
    public function store(Request $request)
    {
        $apiKey = $request->input('key') ?: $request->input('api_key') ?: $request->header('X-API-KEY') ?: $request->bearerToken();

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'API key is required. Pass key in body, query, or X-API-KEY header.'
            ], 400);
        }

        // 1. Check if matches custom .env BLOG_API_KEY
        $envBlogKey = env('BLOG_API_KEY');
        $isEnvMatch = $envBlogKey && ($apiKey === $envBlogKey);

        $user = null;
        if (!$isEnvMatch) {
            // 2. Validate Admin / Active User by API Key
            $user = User::where('api_key', $apiKey)->where('role', 'admin')->first();
            if (!$user) {
                $user = User::where('api_key', $apiKey)->where('status', 'active')->first();
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Invalid or missing Admin API key.'
                ], 401);
            }
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'excerpt' => 'nullable|string|max:500',
            'author' => 'nullable|string|max:100',
            'image' => 'nullable|string',
            'image_url' => 'nullable|url',
            'status' => 'nullable|in:published,draft',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ], [
            'title.required' => 'The blog post title is required.',
            'content.required' => 'The blog post HTML/text content is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $title = trim($request->input('title'));
        $content = $request->input('content');
        $slug = Blog::generateUniqueSlug($title);
        $excerpt = $request->input('excerpt') ?: Str::limit(strip_tags($content), 160);
        $imageUrl = $request->input('image_url') ?: $request->input('image');

        $blog = Blog::create([
            'title' => $title,
            'slug' => $slug,
            'category' => $request->input('category', 'General'),
            'excerpt' => $excerpt,
            'content' => $content,
            'image' => $imageUrl,
            'author' => $request->input('author', 'Admin'),
            'status' => $request->input('status', 'published'),
            'meta_title' => $request->input('meta_title') ?: $title,
            'meta_description' => $request->input('meta_description') ?: $excerpt,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Blog article published successfully via API!',
            'blog' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'category' => $blog->category,
                'author' => $blog->author,
                'status' => $blog->status,
                'url' => url('/blog/' . $blog->slug),
                'created_at' => $blog->created_at->toIso8601String(),
            ]
        ], 201);
    }
}
