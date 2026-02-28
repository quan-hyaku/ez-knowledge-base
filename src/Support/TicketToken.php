<?php

namespace EzKnowledgeBase\Support;

use EzKnowledgeBase\Models\KbTicket;

class TicketToken
{
    public static function generate(KbTicket $ticket): string
    {
        $payload = $ticket->id . '.' . time();
        $signature = hash_hmac('sha256', $payload, static::secret());

        return rtrim(strtr(base64_encode($payload . '.' . $signature), '+/', '-_'), '=');
    }

    public static function verify(string $token): ?KbTicket
    {
        $decoded = base64_decode(strtr($token, '-_', '+/'));

        if (! $decoded) {
            return null;
        }

        $parts = explode('.', $decoded, 3);

        if (count($parts) !== 3) {
            return null;
        }

        [$ticketId, $timestamp, $signature] = $parts;

        $expected = hash_hmac('sha256', $ticketId . '.' . $timestamp, static::secret());

        if (! hash_equals($expected, $signature)) {
            return null;
        }

        $ttl = config('kb.reply.token_ttl', 2592000);

        if ((time() - (int) $timestamp) > $ttl) {
            return null;
        }

        return KbTicket::find($ticketId);
    }

    protected static function secret(): string
    {
        return config('kb.reply.token_secret') ?: config('app.key');
    }
}
