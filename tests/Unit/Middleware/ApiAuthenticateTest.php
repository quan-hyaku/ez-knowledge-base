<?php

namespace EzKnowledgeBase\Tests\Unit\Middleware;

use EzKnowledgeBase\Http\Middleware\ApiAuthenticate;
use EzKnowledgeBase\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiAuthenticateTest extends TestCase
{
    private ApiAuthenticate $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new ApiAuthenticate();
    }

    public function test_request_with_correct_api_key_passes(): void
    {
        config(['kb.api.key' => 'test-secret-key']);

        $request = Request::create('/api/kb', 'GET');
        $request->headers->set('X-KB-API-Key', 'test-secret-key');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['success' => true], $response->getData(true));
    }

    public function test_request_with_wrong_api_key_returns_401(): void
    {
        config(['kb.api.key' => 'test-secret-key']);

        $request = Request::create('/api/kb', 'GET');
        $request->headers->set('X-KB-API-Key', 'wrong-key');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('Unauthenticated.', $response->getData(true)['message']);
    }

    public function test_request_with_no_auth_returns_401(): void
    {
        config(['kb.api.key' => 'test-secret-key']);

        $request = Request::create('/api/kb', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_null_api_key_skips_key_check(): void
    {
        config(['kb.api.key' => null]);

        $request = Request::create('/api/kb', 'GET');

        // With null api key and no bearer token, it should still return 401
        // because neither auth method succeeds
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        // When key is null, `$apiKey && ...` is false, so falls through to bearer check
        // No bearer token either, so 401
        $this->assertEquals(401, $response->getStatusCode());
    }
}
