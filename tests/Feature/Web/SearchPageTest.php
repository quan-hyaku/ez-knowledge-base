<?php

namespace EzKnowledgeBase\Tests\Feature\Web;

use EzKnowledgeBase\Models\KbArticle;
use EzKnowledgeBase\Models\KbCategory;
use EzKnowledgeBase\Tests\TestCase;

class SearchPageTest extends TestCase
{
    public function test_search_with_empty_query_returns_published_articles(): void
    {
        $category = KbCategory::create([
            'name' => 'General',
            'slug' => 'general',
            'is_active' => true,
        ]);

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Published Article',
            'slug' => 'published-article',
            'body' => 'Content',
            'is_published' => true,
        ]);

        $response = $this->get('/help-center/search?q=');

        $response->assertStatus(200);
        $response->assertViewHas('articles');
    }

    public function test_categories_are_passed_to_view(): void
    {
        KbCategory::create([
            'name' => 'General',
            'slug' => 'general',
            'is_active' => true,
        ]);

        $response = $this->get('/help-center/search');

        $response->assertStatus(200);
        $response->assertViewHas('categories');
    }
}
