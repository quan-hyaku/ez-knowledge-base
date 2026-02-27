<?php

namespace EzKnowledgeBase\Tests\Unit;

use EzKnowledgeBase\Http\Middleware\ApiAuthenticate;
use EzKnowledgeBase\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class ServiceProviderTest extends TestCase
{
    public function test_config_is_merged_under_kb_key(): void
    {
        $this->assertNotNull(config('kb'));
        $this->assertIsArray(config('kb'));
    }

    public function test_default_config_values(): void
    {
        $this->assertNull(config('kb.api.key'));
        $this->assertEquals(60, config('kb.api.rate_limit'));
        $this->assertEquals('Weeklify', config('kb.brand.name'));
    }

    public function test_middleware_alias_is_registered(): void
    {
        $router = $this->app['router'];
        $middleware = $router->getMiddleware();

        $this->assertArrayHasKey('kb.api.auth', $middleware);
        $this->assertEquals(ApiAuthenticate::class, $middleware['kb.api.auth']);
    }

    public function test_web_routes_are_registered(): void
    {
        $expectedRoutes = [
            'kb.landing',
            'kb.categories',
            'kb.category',
            'kb.article',
            'kb.search',
            'kb.ticket.create',
            'kb.ticket.store',
            'kb.article.feedback',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route [{$routeName}] is not registered."
            );
        }
    }

    public function test_api_routes_are_registered(): void
    {
        $expectedRoutes = [
            'kb.api.home',
            'kb.api.category',
            'kb.api.article',
            'kb.api.search',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route [{$routeName}] is not registered."
            );
        }
    }

    public function test_views_namespace_is_registered(): void
    {
        $hints = $this->app['view']->getFinder()->getHints();

        $this->assertArrayHasKey('kb', $hints);
    }

    public function test_migrations_are_loaded(): void
    {
        // After running migrations in defineDatabaseMigrations, tables should exist
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('kb_categories'),
            'kb_categories table should exist'
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('kb_articles'),
            'kb_articles table should exist'
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('kb_tags'),
            'kb_tags table should exist'
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('kb_article_tag'),
            'kb_article_tag table should exist'
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('kb_tickets'),
            'kb_tickets table should exist'
        );
    }
}
