<?php

namespace EzKnowledgeBase\Tests\Feature\Web;

use EzKnowledgeBase\Models\KbArticle;
use EzKnowledgeBase\Models\KbCategory;
use EzKnowledgeBase\Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_returns_200(): void
    {
        $response = $this->get('/help-center');

        $response->assertStatus(200);
    }

    public function test_landing_page_has_categories_and_featured_articles(): void
    {
        $category = KbCategory::create([
            'name' => 'Getting Started',
            'slug' => 'getting-started',
            'is_active' => true,
        ]);

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Featured Article',
            'slug' => 'featured-article',
            'body' => 'Content here',
            'is_published' => true,
            'is_featured' => true,
        ]);

        $response = $this->get('/help-center');

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('featuredArticles');
    }
}
