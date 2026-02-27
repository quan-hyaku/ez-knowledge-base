<?php

namespace EzKnowledgeBase\Tests\Feature\Web;

use App\Models\KbArticle;
use App\Models\KbCategory;
use EzKnowledgeBase\Tests\TestCase;

class FeedbackTest extends TestCase
{
    private KbArticle $article;

    protected function setUp(): void
    {
        parent::setUp();

        $category = KbCategory::create([
            'name' => 'General',
            'slug' => 'general',
            'is_active' => true,
        ]);

        $this->article = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'slug' => 'test-article',
            'body' => 'Content',
            'is_published' => true,
            'helpful_yes_count' => 0,
            'helpful_no_count' => 0,
        ]);
    }

    public function test_vote_yes_increments_helpful_yes_count(): void
    {
        $response = $this->postJson("/help-center/article/{$this->article->id}/feedback", [
            'vote' => 'yes',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $this->article->fresh()->helpful_yes_count);
    }

    public function test_vote_no_increments_helpful_no_count(): void
    {
        $response = $this->postJson("/help-center/article/{$this->article->id}/feedback", [
            'vote' => 'no',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $this->article->fresh()->helpful_no_count);
    }

    public function test_invalid_article_id_returns_404(): void
    {
        $response = $this->postJson('/help-center/article/99999/feedback', [
            'vote' => 'yes',
        ]);

        $response->assertStatus(404);
    }
}
