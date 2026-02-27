<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbTag extends Model
{
    protected $table = 'kb_tags';

    protected $fillable = [
        'name',
        'slug',
    ];
}
