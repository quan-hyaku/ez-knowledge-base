<?php

namespace EzKnowledgeBase\Tests\Feature\Api;

use EzKnowledgeBase\Tests\TestCase;

class ApiHomeTest extends TestCase
{
    public function test_unauthenticated_request_returns_401(): void
    {
        config(['kb.api.key' => 'secret-key']);

        $response = $this->getJson('/api/kb');

        $response->assertStatus(401);
    }

    public function test_authenticated_request_returns_correct_json_structure(): void
    {
        config(['kb.api.key' => 'secret-key']);

        $response = $this->getJson('/api/kb', [
            'X-KB-API-Key' => 'secret-key',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'categories',
                'featured_articles',
            ],
        ]);
    }
}
