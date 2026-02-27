<?php

namespace EzKnowledgeBase\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\KbTicket;
use Illuminate\Http\Request;

class TicketController
{
    public function create()
    {
        $categories = KbCategory::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $featuredArticles = KbArticle::where('is_published', true)
            ->with('category')
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->get();

        return view('kb::ticket', compact('categories', 'featuredArticles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string',
            'urgency' => 'nullable|in:low,medium,high',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        KbTicket::create($validated);

        return redirect()->back()->with('success', 'Support ticket created successfully! We will get back to you soon.');
    }
}
