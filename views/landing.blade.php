@extends('kb::layout')

@section('title', 'Knowledge Base | ' . config('kb.brand.name'))

@section('content')
<!-- Hero Section -->
<section class="relative py-20 overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-primary/5 to-transparent"></div>
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-6">{{ config('kb.hero.title') }}</h1>
        <p class="text-lg text-slate-600 dark:text-slate-400 mb-10 max-w-2xl mx-auto">{{ config('kb.hero.subtitle') }}</p>
        <div class="max-w-3xl mx-auto relative group">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                <span class="material-icons text-slate-400">search</span>
            </div>
            <form action="{{ route('kb.search') }}" method="GET" class="flex">
                <label for="landing-search" class="sr-only">Search knowledge base</label>
                <input id="landing-search" class="flex-1 pl-14 pr-4 py-5 bg-white dark:bg-slate-800 border-none rounded-l-xl search-shadow focus:ring-2 focus:ring-primary/20 text-slate-900 dark:text-white transition-all outline-none" placeholder="{{ config('kb.search.hero_placeholder') }}" type="text" name="q" required aria-label="Search knowledge base"/>
                <button type="submit" class="px-6 bg-primary text-white rounded-r-xl font-medium hover:bg-primary/90 transition-colors">Search</button>
            </form>
        </div>
        @if($categories->isNotEmpty())
        <div class="mt-8 flex flex-wrap justify-center gap-3 text-sm">
            <span class="text-slate-500">Popular:</span>
            @foreach($categories->take(3) as $category)
            <a class="px-3 py-1 bg-white dark:bg-slate-800 border border-primary/20 rounded-full hover:border-primary text-slate-600 dark:text-slate-400 transition-colors" href="{{ route('kb.category', $category->slug) }}">{{ $category->name }}</a>
            @endforeach
        </div>
        @endif
    </div>
</section>

<!-- Category Grid -->
<section class="py-16 container mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
        <a class="group p-8 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-primary transition-all duration-300 hover:shadow-lg" href="{{ route('kb.category', $category->slug) }}">
            <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-primary transition-colors">
                <span class="material-icons text-primary group-hover:text-white">{{ $category->icon ?? 'help' }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">{{ $category->name }}</h3>
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ $category->description }}</p>
            <div class="mt-6 flex items-center text-primary font-medium text-sm">
                {{ $category->articles_count }} {{ Str::plural('Article', $category->articles_count) }} <span class="material-icons text-sm ml-2 group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </div>
        </a>
        @empty
        <div class="col-span-3 text-center py-12">
            <p class="text-slate-600 dark:text-slate-400">No categories available yet.</p>
        </div>
        @endforelse
    </div>
</section>

<!-- Popular Articles -->
@if($featuredArticles->isNotEmpty())
<section class="py-16 bg-white dark:bg-slate-900/50">
    <div class="container mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Popular Articles</h2>
            <a class="text-primary font-semibold hover:underline flex items-center" href="{{ route('kb.categories') }}">
                View all articles <span class="material-icons text-sm ml-1">arrow_forward</span>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @php
                $half = ceil($featuredArticles->count() / 2);
                $articles = $featuredArticles->all();
            @endphp
            @for($i = 0; $i < 2; $i++)
            <div class="space-y-6">
                @php
                    $start = $i * $half;
                    $end = min($start + $half, count($articles));
                @endphp
                @foreach(array_slice($articles, $start, $end - $start) as $article)
                <a class="flex items-start group @if(!$loop->first) border-t border-slate-100 dark:border-slate-800 pt-6 @endif" href="{{ route('kb.article', [$article->category->slug, $article->slug]) }}">
                    <span class="material-icons text-primary/40 mt-1 mr-4">description</span>
                    <div>
                        <h4 class="font-semibold text-slate-900 dark:text-white group-hover:text-primary transition-colors">{{ $article->title }}</h4>
                        <p class="text-sm text-slate-500 mt-1">{{ $article->excerpt }}</p>
                    </div>
                </a>
                @endforeach
            </div>
            @endfor
        </div>
    </div>
</section>
@endif

<!-- Still Need Help Section -->
<section class="py-20 bg-background-light dark:bg-background-dark">
    <div class="container mx-auto px-6">
        <div class="bg-primary rounded-2xl p-12 text-center text-white relative overflow-hidden shadow-2xl">
            <!-- Abstract decorative element -->
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-96 h-96 bg-black/10 rounded-full blur-3xl"></div>
            <h2 class="text-3xl font-bold mb-4 relative z-10">Still can't find what you're looking for?</h2>
            <p class="text-primary-100 opacity-90 mb-10 max-w-xl mx-auto text-lg relative z-10">Our support team is available 24/7 to help you with any technical or account-related questions.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 relative z-10">
                <a href="{{ route('kb.ticket.create') }}" class="bg-white text-primary px-8 py-4 rounded-xl font-bold hover:bg-slate-50 transition-colors flex items-center justify-center">
                    <span class="material-icons mr-2">mail</span> Contact Support
                </a>
                <button class="bg-primary border-2 border-white/30 text-white px-8 py-4 rounded-xl font-bold hover:bg-white/10 transition-colors flex items-center justify-center">
                    <span class="material-icons mr-2">forum</span> Start Live Chat
                </button>
            </div>
        </div>
    </div>
</section>
@endsection
