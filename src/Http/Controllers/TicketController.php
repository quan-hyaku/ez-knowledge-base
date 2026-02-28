<?php

namespace EzKnowledgeBase\Http\Controllers;

use EzKnowledgeBase\Models\KbArticle;
use EzKnowledgeBase\Models\KbCategory;
use EzKnowledgeBase\Models\KbTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
        // Verify Cloudflare Turnstile if configured
        if (config('services.turnstile.secret_key')) {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $request->input('cf-turnstile-response'),
                'remoteip' => $request->ip(),
            ]);

            if (! $response->json('success')) {
                return back()->withErrors(['turnstile' => 'Please complete the verification.'])->withInput();
            }
        }

        $user = $request->user();

        $rules = [
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'category' => 'nullable|string|max:255',
            'urgency' => 'nullable|in:low,medium,high',
        ];

        if (! $user) {
            $rules['name'] = 'required|string|max:255';
            $rules['email'] = 'required|email';
        }

        $validated = $request->validate($rules);

        if ($user) {
            $validated['user_id'] = $user->id;
            $validated['name'] = $validated['name'] ?? $user->name;
            $validated['email'] = $validated['email'] ?? $user->email;
        }

        KbTicket::create($validated);

        return redirect()->back()->with('success', 'Support ticket created successfully! We will get back to you soon.');
    }
}
