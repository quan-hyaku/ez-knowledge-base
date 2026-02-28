<?php

namespace EzKnowledgeBase\Traits;

use EzKnowledgeBase\Events\KbTicketReplied;
use EzKnowledgeBase\Models\KbTicket;
use EzKnowledgeBase\Models\KbTicketReply;

trait AdminKbTicket
{
    public function replyToKbTicketAsAdmin(KbTicket $ticket, string $body): KbTicketReply
    {
        $reply = $ticket->replies()->create([
            'body' => $body,
            'is_admin' => true,
            'user_id' => $this->id,
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        event(new KbTicketReplied($ticket, $reply));

        return $reply;
    }

    public function changeKbTicketStatus(KbTicket $ticket, string $status): bool
    {
        if (! in_array($status, ['open', 'in_progress', 'resolved', 'closed'])) {
            return false;
        }

        return $ticket->update(['status' => $status]);
    }

    public function resolveKbTicket(KbTicket $ticket): bool
    {
        return $this->changeKbTicketStatus($ticket, 'resolved');
    }

    public function isKbAdmin(): bool
    {
        return in_array($this->email, config('kb.users.admins', []));
    }
}
