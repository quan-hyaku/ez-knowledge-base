<?php

namespace Packages\EzKnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/kb.php', 'kb');

        // Register API auth middleware alias
        $this->app['router']->aliasMiddleware(
            'kb.api.auth',
            \Packages\EzKnowledgeBase\Middleware\ApiAuthenticate::class
        );
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/web.php');
        $this->loadRoutesFrom(__DIR__ . '/api.php');
        $this->loadViewsFrom(__DIR__ . '/views', 'kb');

        // Publish config so the host app can override branding
        $this->publishes([
            __DIR__ . '/config/kb.php' => config_path('kb.php'),
        ], 'kb-config');

        // Publish default logo asset
        $this->publishes([
            __DIR__ . '/assets/KB-logo.png' => public_path('vendor/kb/KB-logo.png'),
        ], 'kb-assets');

        // Register cache invalidation listeners
        KbCategory::saved(function (KbCategory $category) {
            Cache::forget('kb_categories_with_counts');
            Cache::forget('kb_all_categories_with_top_articles');
            Cache::forget('kb_featured_articles');
        });

        KbCategory::deleted(function (KbCategory $category) {
            Cache::forget('kb_categories_with_counts');
            Cache::forget('kb_all_categories_with_top_articles');
            Cache::forget('kb_featured_articles');
        });

        KbArticle::saved(function (KbArticle $article) {
            // Skip cache invalidation for tracking-only column changes (view_count, helpful counts)
            $trackingColumns = ['view_count', 'helpful_yes_count', 'helpful_no_count'];
            $changedKeys = array_keys($article->getChanges());
            $meaningfulChanges = array_diff($changedKeys, array_merge($trackingColumns, ['updated_at']));

            if (empty($meaningfulChanges)) {
                return;
            }

            Cache::forget('kb_categories_with_counts');
            Cache::forget('kb_all_categories_with_top_articles');
            Cache::forget('kb_featured_articles');
            Cache::forget('kb_article_' . $article->slug);
        });

        KbArticle::deleted(function (KbArticle $article) {
            Cache::forget('kb_categories_with_counts');
            Cache::forget('kb_all_categories_with_top_articles');
            Cache::forget('kb_featured_articles');
            Cache::forget('kb_article_' . $article->slug);
        });
    }
}
