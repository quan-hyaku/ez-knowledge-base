<?php

namespace EzKnowledgeBase\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use EzKnowledgeBase\Models\KbTicketReply;
use EzKnowledgeBase\Support\TicketToken;

class InboundWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        $processed = 0;

        foreach ($items as $item) {
            if ($this->processItem($item)) {
                $processed++;
            }
        }

        return response()->json(['processed' => $processed]);
    }

    protected function processItem(array $item): bool
    {
        $toAddresses = $item['To'] ?? $item['to'] ?? [];
        $token = $this->extractToken($toAddresses);

        if (! $token) {
            return false;
        }

        $ticket = TicketToken::verify($token);

        if (! $ticket) {
            return false;
        }

        $senderEmail = $this->extractSenderEmail($item);

        if (! $senderEmail || mb_strtolower($senderEmail) !== mb_strtolower($ticket->email)) {
            return false;
        }

        $spamScore = (float) ($item['SpamScore'] ?? $item['spam_score'] ?? 0);
        $threshold = config('kb.reply.spam_threshold', 5.0);

        if ($spamScore >= $threshold) {
            return false;
        }

        if ($ticket->status === 'closed') {
            return false;
        }

        $body = $item['RawHtmlBody'] ?? $item['TextBody'] ?? $item['raw_html_body'] ?? $item['text_body'] ?? '';

        if (empty(trim($body))) {
            return false;
        }

        $body = mb_substr(strip_tags($body), 0, 50000);

        KbTicketReply::create([
            'kb_ticket_id' => $ticket->id,
            'body' => $body,
            'is_admin' => false,
            'user_id' => null,
        ]);

        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'open']);
        }

        return true;
    }

    protected function extractToken($toAddresses): ?string
    {
        if (is_string($toAddresses)) {
            return $this->parseTokenFromEmail($toAddresses);
        }

        if (is_array($toAddresses)) {
            foreach ($toAddresses as $entry) {
                $email = is_array($entry) ? ($entry['Address'] ?? $entry['address'] ?? '') : $entry;
                $token = $this->parseTokenFromEmail($email);
                if ($token) {
                    return $token;
                }
            }
        }

        return null;
    }

    protected function parseTokenFromEmail(string $email): ?string
    {
        if (preg_match('/^ticket\+([^@]+)@/', $email, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function extractSenderEmail(array $item): ?string
    {
        $from = $item['From'] ?? $item['from'] ?? null;

        if (is_string($from)) {
            if (preg_match('/<([^>]+)>/', $from, $matches)) {
                return $matches[1];
            }
            return filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : null;
        }

        if (is_array($from)) {
            return $from['Address'] ?? $from['address'] ?? null;
        }

        return null;
    }
}
