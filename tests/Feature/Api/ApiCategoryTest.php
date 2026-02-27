<?php

namespace EzKnowledgeBase\Tests\Feature\Api;

use App\Models\KbArticle;
use App\Models\KbCategory;
use EzKnowledgeBase\Tests\TestCase;

class ApiCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['kb.api.key' => 'secret-key']);
    }

    public function test_returns_category_with_paginated_articles(): void
    {
        $category = KbCategory::create([
            'name' => 'Guides',
            'slug' => 'guides',
            'is_active' => true,
        ]);

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'First Guide',
            'slug' => 'first-guide',
            'body' => 'Content',
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/kb/categories/guides', [
            'X-KB-API-Key' => 'secret-key',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'category' => ['id', 'name', 'slug'],
                'articles',
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
    }

    public function test_inactive_category_returns_404(): void
    {
        KbCategory::create([
            'name' => 'Hidden',
            'slug' => 'hidden',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/kb/categories/hidden', [
            'X-KB-API-Key' => 'secret-key',
        ]);

        $response->assertStatus(404);
    }
}
