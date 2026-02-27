<?php

namespace Packages\EzKnowledgeBase\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackArticleView
{
    /**
     * Track unique article views using session storage.
     * Only increments once per session per article.
     * Reuses the article resolved by the controller (via request attributes)
     * to avoid redundant database queries.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests that returned 200
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return $response;
        }

        // Reuse the article already resolved by the controller
        $article = $request->attributes->get('kb_article');

        if (! $article) {
            return $response;
        }

        // Build a unique session key for this article
        $categorySlug = $request->route('category_slug');
        $articleSlug = $request->route('slug');
        $sessionKey = 'kb_viewed_' . $categorySlug . '_' . $articleSlug;

        // Only count if not already viewed in this session
        if (! $request->session()->has($sessionKey)) {
            DB::table('kb_articles')->where('id', $article->id)->increment('view_count');
            $request->session()->put($sessionKey, true);
        }

        return $response;
    }
}
