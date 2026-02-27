<?php

namespace Packages\EzKnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/kb.php', 'kb');

        // Register API middleware aliases
        $this->app['router']->aliasMiddleware(
            'kb.api.auth',
            \Packages\EzKnowledgeBase\Middleware\ApiAuthenticate::class
        );
        $this->app['router']->aliasMiddleware(
            'kb.api.errors',
            \Packages\EzKnowledgeBase\Middleware\ApiExceptionHandler::class
        );
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/web.php');
        $this->loadRoutesFrom(__DIR__ . '/api.php');
        $this->loadViewsFrom(__DIR__ . '/views', 'kb');

        $this->validateConfigValues();

        // Publish config so the host app can override branding
        $this->publishes([
            __DIR__ . '/config/kb.php' => config_path('kb.php'),
        ], 'kb-config');

        // Publish assets (logo and compiled CSS)
        $this->publishes([
            __DIR__ . '/assets/KB-logo.png' => public_path('vendor/kb/KB-logo.png'),
            __DIR__ . '/assets/css/kb.css' => public_path('vendor/kb/css/kb.css'),
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

            // If slug changed, also invalidate the old slug's cache key
            $originalSlug = $article->getOriginal('slug');
            if ($originalSlug && $originalSlug !== $article->slug) {
                Cache::forget('kb_article_' . $originalSlug);
            }
        });

        KbArticle::deleted(function (KbArticle $article) {
            Cache::forget('kb_categories_with_counts');
            Cache::forget('kb_all_categories_with_top_articles');
            Cache::forget('kb_featured_articles');
            Cache::forget('kb_article_' . $article->slug);
        });
    }

    /**
     * Validate config values used in CSS/JS contexts to prevent injection.
     */
    private function validateConfigValues(): void
    {
        $hexPattern = '/^#[0-9A-Fa-f]{6}$/';

        $colorKeys = [
            'kb.colors.primary' => '#0369A1',
            'kb.colors.background_light' => '#f6f6f8',
            'kb.colors.background_dark' => '#101622',
        ];

        foreach ($colorKeys as $key => $default) {
            $value = config($key);
            if ($value !== null && !preg_match($hexPattern, $value)) {
                Log::warning("Invalid KB config value for '{$key}': '{$value}'. Must be a 6-digit hex color (e.g. #0369A1). Falling back to default '{$default}'.");
                config([$key => $default]);
            }
        }

        $fontFamily = config('kb.font.family');
        if ($fontFamily !== null && !preg_match('/^[a-zA-Z0-9 ]+$/', $fontFamily)) {
            $default = 'Inter';
            Log::warning("Invalid KB config value for 'kb.font.family': '{$fontFamily}'. Must contain only letters, numbers, and spaces. Falling back to default '{$default}'.");
            config(['kb.font.family' => $default]);
        }
    }
}
