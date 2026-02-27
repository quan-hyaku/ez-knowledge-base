@extends('kb::layout')

@section('title', 'All Categories - Knowledge Base')

@section('content')
<!-- HERO SEARCH SECTION -->
<header class="bg-primary py-16 sm:py-24 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -mr-20 -mt-20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-white rounded-full -ml-10 -mb-10 blur-3xl"></div>
    </div>
    <div class="max-w-4xl mx-auto px-4 relative z-10 text-center">
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-6">How can we help you today?</h1>
        <div class="relative group">
            <span class="material-icons absolute left-5 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">search</span>
            <form action="{{ route('kb.search') }}" method="GET">
                <label for="categories-search" class="sr-only">Search knowledge base</label>
                <input id="categories-search" name="q" class="w-full pl-14 pr-6 py-4 sm:py-5 rounded-xl border-none shadow-2xl focus:ring-4 focus:ring-primary/50 text-slate-900 text-lg" placeholder="Search for articles, features, or troubleshooting..." type="text" aria-label="Search knowledge base"/>
                <button class="absolute right-3 top-1/2 -translate-y-1/2 bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primary/90 transition-colors hidden sm:block">Search</button>
            </form>
        </div>
    </div>
</header>

<!-- MAIN CONTENT -->
<section class="w-full px-6 lg:px-10 py-12">
    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" class="mb-8 text-sm">
        <ol class="flex items-center gap-2">
            <li><a href="{{ route('kb.landing') }}" class="text-slate-600 dark:text-slate-400 hover:text-primary">Home</a></li>
            <li><span class="text-slate-400" aria-hidden="true">/</span></li>
            <li aria-current="page"><span class="text-slate-900 dark:text-white font-medium">All Categories</span></li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- LEFT SIDEBAR -->
        <div class="lg:col-span-1">
            <!-- Trending Topics -->
            <div class="bg-white dark:bg-background-dark/50 border border-slate-200 dark:border-slate-800 rounded-xl p-6 mb-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Trending Topics</h2>
                @if($categories->isNotEmpty() && $categories->first()->articles->isNotEmpty())
                    <ul class="space-y-3">
                        @foreach($categories->first()->articles->take(3) as $article)
                            <li>
                                <a href="{{ route('kb.article', [$categories->first()->slug, $article->slug]) }}" class="text-sm text-slate-600 dark:text-slate-400 hover:text-primary flex items-center gap-2 transition-colors">
                                    <span class="material-icons text-xs" aria-hidden="true">trending_up</span>
                                    {{ $article->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400">No articles available yet.</p>
                @endif
            </div>

            <!-- Need more help? -->
            <div class="bg-gradient-to-br from-primary/10 to-primary/5 dark:from-primary/20 dark:to-primary/10 border border-primary/20 rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Need more help?</h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">Can't find what you're looking for? Our team is here to help.</p>
                <a href="{{ route('kb.ticket.create') }}" class="block w-full text-center px-4 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-primary/90 transition-colors">
                    Submit a Ticket
                </a>
            </div>
        </div>

        <!-- CATEGORIES GRID -->
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @forelse($categories as $category)
                    <div class="bg-white dark:bg-background-dark/50 border border-slate-200 dark:border-slate-800 rounded-xl p-6 hover:shadow-xl hover:shadow-primary/5 transition-all">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-primary/10 dark:bg-primary/20 text-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="material-icons text-2xl" aria-hidden="true">{{ $category->icon ?? 'help' }}</span>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $category->name }}</h2>
                                <p class="text-sm text-slate-500">{{ $category->description }}</p>
                            </div>
                        </div>
                        <ul class="space-y-3 mb-6">
                            @foreach($category->articles->take(4) as $article)
                                <li>
                                    <a class="text-slate-600 dark:text-slate-400 hover:text-primary flex items-center gap-2 transition-colors" href="{{ route('kb.article', [$category->slug, $article->slug]) }}">
                                        <span class="material-icons text-xs" aria-hidden="true">description</span>
                                        {{ $article->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <a class="text-primary font-semibold text-sm flex items-center group" href="{{ route('kb.category', $category->slug) }}">
                            View All Articles <span class="material-icons text-sm ml-1 group-hover:translate-x-1 transition-transform" aria-hidden="true">arrow_forward</span>
                        </a>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="bg-white dark:bg-background-dark/50 border border-slate-200 dark:border-slate-800 rounded-xl p-12 text-center">
                            <span class="material-icons text-5xl text-slate-300 dark:text-slate-600 mb-4 block" aria-hidden="true">folder_open</span>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2">No Categories Found</h2>
                            <p class="text-slate-600 dark:text-slate-400">Categories are being set up. Please check back soon.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<!-- SUPPORT BANNER -->
<section class="w-full px-6 lg:px-10 py-12">
    <div class="bg-slate-900 dark:bg-slate-800 rounded-2xl p-8 sm:p-12 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="text-center md:text-left">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">Still looking for answers?</h2>
            <p class="text-slate-400">Join our community or speak with an expert directly.</p>
        </div>
        <div class="flex flex-wrap justify-center gap-4">
            <a class="px-6 py-3 bg-white text-slate-900 rounded-lg font-bold hover:bg-slate-100 transition-colors" href="{{ route('kb.landing') }}">Browse All</a>
            <a class="px-6 py-3 bg-primary text-white rounded-lg font-bold hover:bg-primary/90 transition-colors" href="{{ route('kb.ticket.create') }}">Contact Support</a>
        </div>
    </div>
</section>
@endsection
