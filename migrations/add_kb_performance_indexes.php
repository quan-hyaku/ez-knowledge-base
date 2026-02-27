<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add performance indexes to knowledge base tables.
     *
     * These indexes improve query performance for common access patterns:
     * - Category listing (active categories sorted by sort_order)
     * - Article filtering (published/featured articles)
     * - Article sorting by popularity (view_count)
     */
    public function up(): void
    {
        Schema::table('kb_categories', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'kb_categories_active_sort_index');
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            $table->index(['is_published', 'is_featured'], 'kb_articles_published_featured_index');
            $table->index('view_count', 'kb_articles_view_count_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kb_categories', function (Blueprint $table) {
            $table->dropIndex('kb_categories_active_sort_index');
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropIndex('kb_articles_published_featured_index');
            $table->dropIndex('kb_articles_view_count_index');
        });
    }
};
