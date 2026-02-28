<?php

namespace EzKnowledgeBase\Traits;

use EzKnowledgeBase\Models\KbTicket;
use EzKnowledgeBase\Models\KbTicketReply;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait CanKbTicket
{
    public function kbTickets(): HasMany
    {
        return $this->hasMany(KbTicket::class, 'user_id');
    }

    public function createKbTicket(array $data): KbTicket
    {
        return $this->kbTickets()->create(array_merge($data, [
            'name' => $data['name'] ?? $this->name,
            'email' => $data['email'] ?? $this->email,
        ]));
    }

    public function replyToKbTicket(KbTicket $ticket, string $body): KbTicketReply
    {
        abort_unless($this->ownsKbTicket($ticket), 403, 'You do not own this ticket.');

        return $ticket->replies()->create([
            'body' => $body,
            'is_admin' => false,
            'user_id' => $this->id,
        ]);
    }

    public function ownsKbTicket(KbTicket $ticket): bool
    {
        return $ticket->user_id === $this->id;
    }
}
