<?php

namespace EzKnowledgeBase\Tests\Feature\Web;

use App\Models\KbCategory;
use EzKnowledgeBase\Tests\TestCase;

class CategoryPageTest extends TestCase
{
    public function test_active_category_returns_200(): void
    {
        KbCategory::create([
            'name' => 'Guides',
            'slug' => 'guides',
            'is_active' => true,
        ]);

        $response = $this->get('/help-center/category/guides');

        $response->assertStatus(200);
    }

    public function test_inactive_category_returns_404(): void
    {
        KbCategory::create([
            'name' => 'Hidden',
            'slug' => 'hidden',
            'is_active' => false,
        ]);

        $response = $this->get('/help-center/category/hidden');

        $response->assertStatus(404);
    }

    public function test_nonexistent_slug_returns_404(): void
    {
        $response = $this->get('/help-center/category/does-not-exist');

        $response->assertStatus(404);
    }
}
