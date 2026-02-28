<?php

namespace EzKnowledgeBase\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBrevoWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('kb.reply.webhook_secret');

        if (! $secret) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $provided = $request->header('X-Brevo-Secret');

        if (! $provided || ! hash_equals($secret, $provided)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
