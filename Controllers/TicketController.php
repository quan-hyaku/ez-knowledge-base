<?php

namespace Packages\EzKnowledgeBase\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\KbTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TicketController
{
    public function create()
    {
        $categories = KbCategory::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $featuredArticles = Cache::remember(
            'kb_featured_articles',
            3600,
            function () {
                return KbArticle::where('is_published', true)
                    ->where('is_featured', true)
                    ->with('category')
                    ->limit(6)
                    ->get();
            }
        );

        return view('kb::ticket', compact('categories', 'featuredArticles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'category' => 'nullable|in:billing,technical,feature,general,other',
            'urgency' => 'nullable|in:low,medium,high',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        KbTicket::create($validated);

        return redirect()->back()->with('success', 'Support ticket created successfully! We will get back to you soon.');
    }
}
