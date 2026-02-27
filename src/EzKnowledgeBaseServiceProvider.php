<?php

namespace EzKnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class EzKnowledgeBaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/kb.php', 'kb');

        // Register API auth middleware alias
        $this->app['router']->aliasMiddleware(
            'kb.api.auth',
            \EzKnowledgeBase\Http\Middleware\ApiAuthenticate::class
        );
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'kb');

        // Publish config so the host app can override branding
        $this->publishes([
            __DIR__ . '/../config/kb.php' => config_path('kb.php'),
        ], 'kb-config');

        // Publish default logo asset
        $this->publishes([
            __DIR__ . '/../public/KB-logo.png' => public_path('vendor/kb/KB-logo.png'),
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
