<?php

namespace EzKnowledgeBase\Tests\Unit\Middleware;

use App\Models\KbArticle;
use App\Models\KbCategory;
use EzKnowledgeBase\Tests\TestCase;

class TrackArticleViewTest extends TestCase
{
    private KbCategory $category;
    private KbArticle $article;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = KbCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $this->article = KbArticle::create([
            'kb_category_id' => $this->category->id,
            'title' => 'Test Article',
            'slug' => 'test-article',
            'body' => '# Hello World',
            'is_published' => true,
            'view_count' => 0,
        ]);
    }

    public function test_first_visit_increments_view_count(): void
    {
        $this->get("/help-center/test-category/test-article");

        $this->assertEquals(1, $this->article->fresh()->view_count);
    }

    public function test_second_visit_in_same_session_does_not_increment(): void
    {
        $this->get("/help-center/test-category/test-article");
        $this->get("/help-center/test-category/test-article");

        $this->assertEquals(1, $this->article->fresh()->view_count);
    }

    public function test_non_get_requests_skip_tracking(): void
    {
        // POST to the article URL shouldn't track views
        // The route only accepts GET, so this would 405, which means status != 200
        $this->post("/help-center/test-category/test-article");

        $this->assertEquals(0, $this->article->fresh()->view_count);
    }

    public function test_inactive_category_skips_tracking(): void
    {
        $this->category->update(['is_active' => false]);

        $this->get("/help-center/test-category/test-article");

        $this->assertEquals(0, $this->article->fresh()->view_count);
    }
}
