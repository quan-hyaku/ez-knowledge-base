<?php

namespace Packages\EzKnowledgeBase\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Packages\EzKnowledgeBase\Helpers\HtmlSanitizer;
use Packages\EzKnowledgeBase\Helpers\MarkdownHelper;

class KnowledgeBaseController
{
    public function landing()
    {
        $categories = Cache::remember(
            'kb_categories_with_counts',
            3600,
            function () {
                return KbCategory::where('is_active', true)
                    ->with(['articles' => function ($query) {
                        $query->where('is_published', true)->limit(5);
                    }])
                    ->withCount(['articles' => function ($query) {
                        $query->where('is_published', true);
                    }])
                    ->get();
            }
        );

        $featuredArticles = Cache::remember(
            'kb_featured_articles',
            3600,
            function () {
                return KbArticle::where('is_published', true)
                    ->where('is_featured', true)
                    ->with('category')
                    ->limit(6)
                    ->get();
            }
        );

        return view('kb::landing', compact('categories', 'featuredArticles'));
    }

    public function categories()
    {
        $categories = Cache::remember(
            'kb_all_categories_with_top_articles',
            3600,
            function () {
                return KbCategory::where('is_active', true)
                    ->with(['articles' => function ($query) {
                        $query->where('is_published', true)
                            ->limit(4)
                            ->orderBy('view_count', 'desc');
                    }])
                    ->get();
            }
        );

        return view('kb::categories', compact('categories'));
    }

    public function category($slug)
    {
        $category = KbCategory::where('slug', $slug)
            ->where('is_active', true)
            ->withCount(['articles' => function ($query) {
                $query->where('is_published', true);
            }])
            ->firstOrFail();

        $articles = $category->articles()
            ->where('is_published', true)
            ->paginate(15);

        // Sidebar: all categories with their published articles (eager-loaded)
        $allCategories = KbCategory::where('is_active', true)
            ->with(['articles' => fn($q) => $q->where('is_published', true)->limit(5)])
            ->orderBy('sort_order')
            ->get();

        // Related categories derived from $allCategories (no extra query)
        $relatedCategories = $allCategories->where('id', '!=', $category->id);

        return view('kb::category', compact('category', 'articles', 'allCategories', 'relatedCategories'));
    }

    public function article($category_slug, $slug)
    {
        $category = KbCategory::where('slug', $category_slug)
            ->where('is_active', true)
            ->firstOrFail();

        $article = Cache::remember(
            'kb_article_' . $slug,
            1800,
            function () use ($category, $slug) {
                return $category->articles()
                    ->where('slug', $slug)
                    ->where('is_published', true)
                    ->firstOrFail();
            }
        );

        // Store the resolved article on the request so TrackArticleView middleware can reuse it
        // instead of re-querying the database
        $request = request();
        $request->attributes->set('kb_article', $article);

        // Get category sidebar navigation
        $sidebarArticles = $category->articles()
            ->where('is_published', true)
            ->orderBy('title', 'asc')
            ->limit(20)
            ->get();

        // Get previous and next articles within the same category (ordered by title)
        $prevArticle = $category->articles()
            ->where('is_published', true)
            ->where('title', '<', $article->title)
            ->orderBy('title', 'desc')
            ->first(['title', 'slug']);

        $nextArticle = $category->articles()
            ->where('is_published', true)
            ->where('title', '>', $article->title)
            ->orderBy('title', 'asc')
            ->first(['title', 'slug']);

        // Parse markdown body to HTML
        $parsedBody = Str::markdown($article->body, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        // Add IDs to headings for anchor links
        $parsedBody = MarkdownHelper::addHeadingIds($parsedBody);

        // Sanitize HTML to prevent XSS from rendered markdown
        $parsedBody = HtmlSanitizer::sanitize($parsedBody);

        // Extract h2 headings for TOC from parsed HTML
        $toc = MarkdownHelper::extractHeadings($parsedBody);

        return view('kb::article', compact('article', 'category', 'sidebarArticles', 'toc', 'parsedBody', 'prevArticle', 'nextArticle'));
    }

    public function feedback(int $id, Request $request)
    {
        $validated = $request->validate([
            'vote' => 'required|in:yes,no',
        ]);

        $article = KbArticle::findOrFail($id);

        // Server-side deduplication via session
        $sessionKey = 'kb_feedback_' . $article->id;
        if ($request->session()->has($sessionKey)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already voted on this article.',
                'helpful_yes_count' => $article->helpful_yes_count,
                'helpful_no_count' => $article->helpful_no_count,
            ], 409);
        }

        if ($validated['vote'] === 'yes') {
            $article->increment('helpful_yes_count');
        } else {
            $article->increment('helpful_no_count');
        }

        $request->session()->put($sessionKey, $validated['vote']);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
            'helpful_yes_count' => $article->helpful_yes_count,
            'helpful_no_count' => $article->helpful_no_count,
        ]);
    }

}
