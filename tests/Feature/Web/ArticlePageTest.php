<?php

namespace EzKnowledgeBase\Tests\Feature\Web;

use App\Models\KbArticle;
use App\Models\KbCategory;
use EzKnowledgeBase\Tests\TestCase;

class ArticlePageTest extends TestCase
{
    private KbCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = KbCategory::create([
            'name' => 'Guides',
            'slug' => 'guides',
            'is_active' => true,
        ]);
    }

    public function test_published_article_returns_200(): void
    {
        KbArticle::create([
            'kb_category_id' => $this->category->id,
            'title' => 'Setup Guide',
            'slug' => 'setup-guide',
            'body' => '# Setup Guide',
            'is_published' => true,
        ]);

        $response = $this->get('/help-center/guides/setup-guide');

        $response->assertStatus(200);
    }

    public function test_markdown_body_is_parsed_to_html(): void
    {
        KbArticle::create([
            'kb_category_id' => $this->category->id,
            'title' => 'Markdown Article',
            'slug' => 'markdown-article',
            'body' => "## Section One\n\nSome paragraph text.\n\n## Section Two\n\nMore text.",
            'is_published' => true,
        ]);

        $response = $this->get('/help-center/guides/markdown-article');

        $response->assertStatus(200);
        $response->assertViewHas('parsedBody');
        $parsedBody = $response->viewData('parsedBody');
        $this->assertStringContainsString('<h2', $parsedBody);
        $this->assertStringContainsString('Section One', $parsedBody);
    }

    public function test_toc_contains_h2_headings(): void
    {
        KbArticle::create([
            'kb_category_id' => $this->category->id,
            'title' => 'TOC Article',
            'slug' => 'toc-article',
            'body' => "## First Heading\n\nText\n\n## Second Heading\n\nMore text",
            'is_published' => true,
        ]);

        $response = $this->get('/help-center/guides/toc-article');

        $response->assertStatus(200);
        $toc = $response->viewData('toc');
        $this->assertCount(2, $toc);
        $this->assertEquals('First Heading', $toc[0]['text']);
        $this->assertEquals('Second Heading', $toc[1]['text']);
    }

    public function test_unpublished_article_returns_404(): void
    {
        KbArticle::create([
            'kb_category_id' => $this->category->id,
            'title' => 'Draft Article',
            'slug' => 'draft-article',
            'body' => 'Draft content',
            'is_published' => false,
        ]);

        $response = $this->get('/help-center/guides/draft-article');

        $response->assertStatus(404);
    }
}
