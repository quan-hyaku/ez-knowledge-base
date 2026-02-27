@extends('kb::layout')

@section('title', $category->name . ' - Knowledge Base')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
    <!-- LEFT SIDEBAR - Category Navigation -->
    <aside aria-label="Category navigation" class="hidden lg:block lg:col-span-3 2xl:col-span-2 h-[calc(100vh-8rem)] sticky top-24 overflow-y-auto pr-4">
        <div class="space-y-8">
            @forelse($allCategories as $cat)
                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">{{ $cat->name }}</h5>
                    <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
                        @foreach($cat->articles as $catArticle)
                            <li>
                                <a class="{{ $cat->id === $category->id ? 'text-primary font-semibold' : 'hover:text-primary transition-colors' }}" href="{{ route('kb.article', [$cat->slug, $catArticle->slug]) }}">{{ $catArticle->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="text-sm text-slate-500">No categories available</p>
            @endforelse
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="lg:col-span-6 2xl:col-span-8">
        <!-- Breadcrumbs -->
        <div class="mb-8 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
            <a href="{{ route('kb.landing') }}" class="hover:text-primary transition-colors">Home</a>
            <span class="text-slate-400">/</span>
            <a href="{{ route('kb.categories') }}" class="hover:text-primary transition-colors">Categories</a>
            <span class="text-slate-400">/</span>
            <span class="text-slate-900 dark:text-white font-medium">{{ $category->name }}</span>
        </div>

        <!-- Category Header -->
        <header class="mb-12 pb-8 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-start gap-4 mb-6">
                @if($category->icon)
                    <div class="flex-shrink-0">
                        <span class="material-icons text-4xl text-primary">{{ $category->icon }}</span>
                    </div>
                @endif
                <div class="flex-grow">
                    <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-3">{{ $category->name }}</h1>
                    @if($category->description)
                        <p class="text-slate-600 dark:text-slate-400 text-lg leading-relaxed">{{ $category->description }}</p>
                    @endif
                </div>
            </div>
        </header>

        <!-- Articles List -->
        @if($articles->count() > 0)
            <div class="space-y-4 mb-12">
                @foreach($articles as $article)
                    <article class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-primary/50 transition-all shadow-sm">
                        <div class="flex flex-col gap-3">
                            <h2 class="text-xl font-semibold text-primary hover:underline">
                                <a href="{{ route('kb.article', [$category->slug, $article->slug]) }}">{{ $article->title }}</a>
                            </h2>
                            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                                {{ $article->excerpt ?: Str::limit(strip_tags($article->body), 150) }}
                            </p>
                            <div class="flex flex-wrap items-center gap-4 pt-3 border-t border-slate-50 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400">
                                <span class="flex items-center gap-1">
                                    <span class="material-icons text-xs">schedule</span>
                                    {{ $article->read_time_minutes ?? 5 }} min read
                                </span>
                                <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                                <span>Updated {{ $article->updated_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mb-12">
                {{ $articles->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <span class="material-icons text-6xl text-slate-300 mb-4 block">article</span>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">No articles found</h2>
                <p class="text-slate-500 dark:text-slate-400 mb-6">This category doesn't have any published articles yet.</p>
                <a href="{{ route('kb.categories') }}" class="inline-block bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                    Browse All Categories
                </a>
            </div>
        @endif
    </main>

    <!-- RIGHT SIDEBAR -->
    <aside aria-label="Category details" class="hidden lg:block lg:col-span-3 2xl:col-span-2 h-[calc(100vh-8rem)] sticky top-24 overflow-y-auto pl-4 border-l border-slate-200 dark:border-slate-800">
        <!-- Category Stats Box -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 mb-8">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-4">Category Stats</h4>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 dark:text-slate-400">Total Articles</span>
                    <span class="text-lg font-bold text-primary">{{ $category->articles_count }}</span>
                </div>
            </div>
        </div>

        <!-- Related Categories -->
        @if($relatedCategories && $relatedCategories->count() > 0)
            <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 mb-8">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-4">Other Categories</h4>
                <ul class="space-y-3">
                    @foreach($relatedCategories as $relCat)
                        <li>
                            <a href="{{ route('kb.category', $relCat->slug) }}" class="text-sm text-slate-600 dark:text-slate-400 hover:text-primary transition-colors flex items-center justify-between">
                                <span>{{ $relCat->name }}</span>
                                <span class="material-icons text-sm">arrow_forward</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Need Help CTA Card -->
        <div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800">
            <div class="bg-primary rounded-xl p-6 text-white relative overflow-hidden group cursor-pointer">
                <div class="relative z-10">
                    <h4 class="font-bold mb-2">Need Help?</h4>
                    <p class="text-xs text-white/80 mb-4 leading-relaxed">Can't find what you're looking for? Talk to our support team.</p>
                    <a href="{{ route('kb.ticket.create') }}" class="bg-white text-primary px-4 py-2 rounded-lg text-xs font-bold hover:bg-background-light transition-colors inline-block">Contact Support</a>
                </div>
                <span class="material-icons absolute -right-4 -bottom-4 text-white/10 text-8xl transform -rotate-12 group-hover:scale-110 transition-transform">support_agent</span>
            </div>
        </div>
    </aside>
</div>
@endsection
