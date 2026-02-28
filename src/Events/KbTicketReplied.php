<?php

namespace EzKnowledgeBase\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use EzKnowledgeBase\Models\KbTicket;
use EzKnowledgeBase\Models\KbTicketReply;

class KbTicketReplied
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public KbTicket $ticket,
        public KbTicketReply $reply,
    ) {}
}
