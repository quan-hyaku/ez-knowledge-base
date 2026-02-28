<?php

namespace EzKnowledgeBase\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KbTicketReply extends Model
{
    use HasFactory;

    protected $table = 'kb_ticket_replies';

    protected $fillable = [
        'kb_ticket_id',
        'body',
        'is_admin',
        'user_id',
        'message_id',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(KbTicket::class, 'kb_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('kb.users.model', 'App\\Models\\User'));
    }
}
