<?php

namespace EzKnowledgeBase\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use EzKnowledgeBase\Events\KbTicketReplied;
use EzKnowledgeBase\Mail\KbTicketReplyMail;
use EzKnowledgeBase\Support\TicketToken;

class SendTicketReplyNotification implements ShouldQueue
{
    public function handle(KbTicketReplied $event): void
    {
        if (! config('kb.reply.enabled')) {
            return;
        }

        if (! $event->reply->is_admin) {
            return;
        }

        $ticket = $event->ticket;

        if (empty($ticket->email)) {
            return;
        }

        $replyTo = null;
        $domain = config('kb.reply.domain');

        if ($domain) {
            $token = TicketToken::generate($ticket);
            $replyTo = 'ticket+' . $token . '@' . $domain;
        }

        $messageId = '<' . Str::uuid() . '@' . ($domain ?: parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost') . '>';

        $event->reply->update(['message_id' => $messageId]);

        Mail::to($ticket->email)->queue(
            new KbTicketReplyMail($ticket, $event->reply, $replyTo, $messageId)
        );
    }
}
