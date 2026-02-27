<?php

namespace Packages\EzKnowledgeBase\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;

class SearchController
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $categoryFilter = $request->input('category', null);

        if (empty(trim($query))) {
            $articles = KbArticle::query()
                ->where('is_published', true)
                ->with('category')
                ->paginate(10);
        } else {
            // Use Laravel Scout full-text search with TNTSearch
            $searchQuery = KbArticle::search($query);

            $articles = $searchQuery->query(function ($builder) use ($categoryFilter) {
                $builder->where('is_published', true)
                    ->with('category');

                if ($categoryFilter) {
                    $builder->whereHas('category', function ($q) use ($categoryFilter) {
                        $q->where('slug', $categoryFilter);
                    });
                }
            })->paginate(10);
        }

        $categories = KbCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('kb::search', compact('articles', 'query', 'categories'));
    }
}
