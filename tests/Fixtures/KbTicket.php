<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbTicket extends Model
{
    protected $table = 'kb_tickets';

    protected $fillable = [
        'subject',
        'category',
        'urgency',
        'description',
        'name',
        'email',
        'status',
    ];
}
