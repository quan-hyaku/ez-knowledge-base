<?php

namespace EzKnowledgeBase\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KbTicket extends Model
{
    use HasFactory;

    protected $table = 'kb_tickets';

    protected $fillable = [
        'subject',
        'category',
        'urgency',
        'description',
        'name',
        'email',
        'status',
        'user_id',
    ];

    public function replies(): HasMany
    {
        return $this->hasMany(KbTicketReply::class, 'kb_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('kb.users.model', 'App\\Models\\User'));
    }
}
