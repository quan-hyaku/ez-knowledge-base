<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbArticle extends Model
{
    protected $table = 'kb_articles';

    protected $fillable = [
        'kb_category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
        'read_time_minutes',
        'is_published',
        'is_featured',
        'helpful_yes_count',
        'helpful_no_count',
        'view_count',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(KbCategory::class, 'kb_category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(KbTag::class, 'kb_article_tag');
    }
}
