<?php

namespace EzKnowledgeBase\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KbArticle extends Model
{
    use HasFactory;

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
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'kb_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(KbTag::class, 'kb_article_tag');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
