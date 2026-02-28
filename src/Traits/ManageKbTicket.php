<?php

namespace EzKnowledgeBase\Traits;

use EzKnowledgeBase\Events\KbTicketReplied;
use EzKnowledgeBase\Models\KbTicket;
use EzKnowledgeBase\Models\KbTicketReply;

trait ManageKbTicket
{
    public function replyToKbTicketAsStaff(KbTicket $ticket, string $body): KbTicketReply
    {
        $reply = $ticket->replies()->create([
            'body' => $body,
            'is_admin' => false,
            'user_id' => $this->id,
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        event(new KbTicketReplied($ticket, $reply));

        return $reply;
    }
}
