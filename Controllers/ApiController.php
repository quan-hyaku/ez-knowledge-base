<?php

namespace Packages\EzKnowledgeBase\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Packages\EzKnowledgeBase\Helpers\HtmlSanitizer;
use Packages\EzKnowledgeBase\Helpers\MarkdownHelper;

class ApiController
{
    /**
     * GET /api/kb
     *
     * Returns categories with article counts and featured/popular articles.
     */
    public function home(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $limit = (int) $request->input('limit', 20);

        $categories = Cache::remember(
            'kb_categories_with_counts',
            3600,
            function () {
                return KbCategory::where('is_active', true)
                    ->withCount(['articles' => function ($query) {
                        $query->where('is_published', true);
                    }])
                    ->orderBy('sort_order')
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

        return response()->json([
            'data' => [
                'categories' => $categories->take($limit)->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'icon' => $category->icon,
                        'sort_order' => $category->sort_order,
                        'articles_count' => $category->articles_count,
                    ];
                }),
                'featured_articles' => $featuredArticles->map(function ($article) {
                    return $this->formatArticleSummary($article);
                }),
            ],
        ]);
    }

    /**
     * GET /api/kb/categories/{slug}
     *
     * Returns a category and its published articles (paginated).
     */
    public function category(string $slug): JsonResponse
    {
        $category = KbCategory::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $articles = $category->articles()
            ->where('is_published', true)
            ->paginate(15);

        return response()->json([
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'icon' => $category->icon,
                ],
                'articles' => $articles->getCollection()->map(function ($article) {
                    return $this->formatArticleSummary($article);
                }),
            ],
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    /**
     * GET /api/kb/categories/{slug}/{article}
     *
     * Returns a single article with parsed HTML body and table of contents.
     */
    public function article(string $categorySlug, string $articleSlug): JsonResponse
    {
        $category = KbCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $article = Cache::remember(
            'kb_article_' . $articleSlug,
            1800,
            function () use ($category, $articleSlug) {
                return $category->articles()
                    ->where('slug', $articleSlug)
                    ->where('is_published', true)
                    ->firstOrFail();
            }
        );

        // Parse markdown to HTML
        $parsedBody = Str::markdown($article->body, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        // Add IDs to headings for anchor links
        $parsedBody = MarkdownHelper::addHeadingIds($parsedBody);

        // Sanitize HTML to prevent XSS
        $parsedBody = HtmlSanitizer::sanitize($parsedBody);

        // Extract h2 headings for table of contents
        $toc = MarkdownHelper::extractHeadings($parsedBody);

        return response()->json([
            'data' => [
                'article' => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'body_markdown' => $article->body,
                    'body_html' => $parsedBody,
                    'toc' => $toc,
                    'meta_title' => $article->meta_title,
                    'meta_description' => $article->meta_description,
                    'read_time_minutes' => $article->read_time_minutes,
                    'is_featured' => $article->is_featured,
                    'view_count' => $article->view_count,
                    'helpful_yes_count' => $article->helpful_yes_count,
                    'helpful_no_count' => $article->helpful_no_count,
                    'published_at' => $article->published_at,
                    'created_at' => $article->created_at,
                    'updated_at' => $article->updated_at,
                ],
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
            ],
        ]);
    }

    /**
     * GET /api/kb/search?q=&category=
     *
     * Full-text search via Laravel Scout. Supports optional category filter.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'nullable|string|max:500',
            'category' => 'nullable|string|exists:kb_categories,slug',
        ]);

        $query = $request->input('q', '');
        $categoryFilter = $request->input('category', null);

        if (empty(trim($query))) {
            $articles = KbArticle::query()
                ->where('is_published', true)
                ->with('category')
                ->paginate(10);
        } else {
            $searchQuery = KbArticle::search($query);

            $articles = $searchQuery->query(function ($builder) use ($categoryFilter) {
                $builder->where('is_published', true)
                    ->with('category');

                if ($categoryFilter) {
                    $builder->whereHas('category', function ($q) use ($categoryFilter) {
                        $q->where('slug', $categoryFilter);
                    });
                }
            })->paginate(10);
        }

        return response()->json([
            'data' => [
                'articles' => collect($articles->items())->map(function ($article) {
                    return $this->formatArticleSummary($article);
                }),
                'query' => $query,
                'category' => $categoryFilter,
            ],
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    /**
     * Format an article for list/summary responses (no body content).
     */
    private function formatArticleSummary(KbArticle $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'read_time_minutes' => $article->read_time_minutes,
            'is_featured' => $article->is_featured,
            'view_count' => $article->view_count,
            'published_at' => $article->published_at,
            'category' => $article->category ? [
                'id' => $article->category->id,
                'name' => $article->category->name,
                'slug' => $article->category->slug,
            ] : null,
        ];
    }

}
