<?php

namespace EzKnowledgeBase\Tests\Feature\Api;

use App\Models\KbArticle;
use App\Models\KbCategory;
use EzKnowledgeBase\Tests\TestCase;

class ApiSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['kb.api.key' => 'secret-key']);
    }

    public function test_empty_query_returns_paginated_articles(): void
    {
        $category = KbCategory::create([
            'name' => 'General',
            'slug' => 'general',
            'is_active' => true,
        ]);

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'slug' => 'test-article',
            'body' => 'Content',
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/kb/search?q=', [
            'X-KB-API-Key' => 'secret-key',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'articles',
                'query',
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }

    public function test_response_has_correct_shape(): void
    {
        $response = $this->getJson('/api/kb/search?q=', [
            'X-KB-API-Key' => 'secret-key',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['articles', 'query'],
            'meta',
        ]);
    }
}
