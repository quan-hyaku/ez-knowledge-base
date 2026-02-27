<?php

namespace Packages\EzKnowledgeBase\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $categoryFilter = $request->input('category', null);
        $sort = $request->input('sort', 'relevance');

        if (!in_array($sort, ['relevance', 'recent', 'oldest'])) {
            $sort = 'relevance';
        }

        if (empty(trim($query))) {
            $builder = KbArticle::query()
                ->where('is_published', true)
                ->with('category');

            if ($categoryFilter) {
                $builder->whereHas('category', function ($q) use ($categoryFilter) {
                    $q->where('slug', $categoryFilter);
                });
            }

            if ($sort === 'recent') {
                $builder->orderBy('updated_at', 'desc');
            } elseif ($sort === 'oldest') {
                $builder->orderBy('updated_at', 'asc');
            } else {
                $builder->orderBy('updated_at', 'desc');
            }

            $articles = $builder->paginate(10);
        } else {
            // Use Laravel Scout full-text search with TNTSearch
            $searchQuery = KbArticle::search($query);

            $articles = $searchQuery->query(function ($builder) use ($categoryFilter, $sort) {
                $builder->where('is_published', true)
                    ->with('category');

                if ($categoryFilter) {
                    $builder->whereHas('category', function ($q) use ($categoryFilter) {
                        $q->where('slug', $categoryFilter);
                    });
                }

                if ($sort === 'recent') {
                    $builder->orderBy('updated_at', 'desc');
                } elseif ($sort === 'oldest') {
                    $builder->orderBy('updated_at', 'asc');
                }
            })->paginate(10);
        }

        $categories = Cache::remember(
            'kb_categories_with_counts',
            3600,
            function () {
                return KbCategory::where('is_active', true)
                    ->with(['articles' => function ($query) {
                        $query->where('is_published', true)->limit(5);
                    }])
                    ->withCount(['articles' => function ($query) {
                        $query->where('is_published', true);
                    }])
                    ->get();
            }
        );

        return view('kb::search', compact('articles', 'query', 'categories', 'categoryFilter', 'sort'));
    }
}
