<?php

namespace EzKnowledgeBase\Tests\Feature\Api;

use App\Models\KbArticle;
use App\Models\KbCategory;
use EzKnowledgeBase\Tests\TestCase;

class ApiArticleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['kb.api.key' => 'secret-key']);
    }

    public function test_returns_full_article_with_body_html_and_toc(): void
    {
        $category = KbCategory::create([
            'name' => 'Guides',
            'slug' => 'guides',
            'is_active' => true,
        ]);

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Setup Guide',
            'slug' => 'setup-guide',
            'body' => "## Getting Started\n\nHello world\n\n## Next Steps\n\nMore content",
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/kb/categories/guides/setup-guide', [
            'X-KB-API-Key' => 'secret-key',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'article' => [
                    'id', 'title', 'slug', 'body_html', 'toc',
                ],
                'category' => ['id', 'name', 'slug'],
            ],
        ]);

        $data = $response->json('data.article');
        $this->assertStringContainsString('<h2', $data['body_html']);
        $this->assertCount(2, $data['toc']);
    }

    public function test_unpublished_article_returns_404(): void
    {
        $category = KbCategory::create([
            'name' => 'Guides',
            'slug' => 'guides',
            'is_active' => true,
        ]);

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Draft',
            'slug' => 'draft',
            'body' => 'Draft content',
            'is_published' => false,
        ]);

        $response = $this->getJson('/api/kb/categories/guides/draft', [
            'X-KB-API-Key' => 'secret-key',
        ]);

        $response->assertStatus(404);
    }
}
