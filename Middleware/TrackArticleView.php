<?php

namespace Packages\EzKnowledgeBase\Middleware;

use App\Models\KbCategory;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackArticleView
{
    /**
     * Track unique article views using session storage.
     * Only increments once per session per article.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests that returned 200
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return $response;
        }

        // Extract route parameters
        $categorySlug = $request->route('category_slug');
        $articleSlug = $request->route('slug');

        if (! $categorySlug || ! $articleSlug) {
            return $response;
        }

        // Build a unique session key for this article
        $sessionKey = 'kb_viewed_' . $categorySlug . '_' . $articleSlug;

        // Only count if not already viewed in this session
        if (! $request->session()->has($sessionKey)) {
            $category = KbCategory::where('slug', $categorySlug)
                ->where('is_active', true)
                ->first();

            if ($category) {
                $article = $category->articles()
                    ->where('slug', $articleSlug)
                    ->where('is_published', true)
                    ->first();

                if ($article) {
                    DB::table('kb_articles')->where('id', $article->id)->increment('view_count');
                    $request->session()->put($sessionKey, true);
                }
            }
        }

        return $response;
    }
}
