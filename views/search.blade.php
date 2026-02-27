@extends('kb::layout')

@section('title', 'Search Knowledge Base')

@section('content')
<div class="flex flex-col lg:flex-row gap-8">
    <!-- Sidebar Filters -->
    <aside aria-label="Search filters" class="w-full lg:w-64 flex-shrink-0">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Categories</h3>
            <div class="space-y-3 mb-6">
                @foreach($categories as $category)
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input
                            type="checkbox"
                            name="categories[]"
                            value="{{ $category->id }}"
                            class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary"
                            {{ request('categories') && in_array($category->id, (array)request('categories')) ? 'checked' : '' }}
                        />
                        <span class="text-sm text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">
                            {{ $category->name }}
                        </span>
                    </label>
                @endforeach
            </div>
            <button class="w-full text-sm font-medium text-primary hover:text-primary/80 transition-colors py-2 px-3 rounded hover:bg-slate-100 dark:hover:bg-slate-800">
                Clear all filters
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 min-w-0">
        <!-- Search Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">
                        Search Results
                    </h1>
                    <p class="text-slate-600 dark:text-slate-400">
                        @if($articles->total() > 0)
                            Found <span class="font-semibold">{{ $articles->total() }}</span>
                            result{{ $articles->total() !== 1 ? 's' : '' }} for "<span class="font-semibold">{{ $query }}</span>"
                        @else
                            No results found for "<span class="font-semibold">{{ $query }}</span>"
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <label for="sort" class="text-sm font-medium text-slate-700 dark:text-slate-300">Sort by:</label>
                    <select id="sort" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="relevance">Relevance</option>
                        <option value="recent">Most Recent</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Results List -->
        @if($articles->count() > 0)
            <div class="space-y-4 mb-8">
                @foreach($articles as $article)
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-primary/50 transition-all shadow-sm">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <a class="text-xl font-semibold text-primary hover:underline" href="{{ route('kb.article', [$article->category->slug, $article->slug]) }}">
                                    {{ $article->title }}
                                </a>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                                {{ Str::limit(strip_tags($article->body), 200) }}
                            </p>
                            <div class="flex flex-wrap items-center justify-between gap-4 pt-2 border-t border-slate-50 dark:border-slate-800">
                                <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ $article->category->name }}</span>
                                    <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                                    <span>Updated {{ $article->updated_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mb-12">
                {{ $articles->appends(['q' => $query])->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <span class="material-icons text-6xl text-slate-300 mb-4 block" aria-hidden="true">search_off</span>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">No results found</h2>
                <p class="text-slate-500 dark:text-slate-400 mb-6">Try different keywords or browse our categories.</p>
                <a href="{{ route('kb.categories') }}" class="inline-block bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                    Browse Categories
                </a>
            </div>
        @endif

        <!-- CTA Section -->
        <div class="mt-16 bg-primary/5 rounded-2xl border-2 border-dashed border-primary/20 p-8 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-16 h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-icons text-3xl" aria-hidden="true">support_agent</span>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Can't find what you're looking for?</h2>
                <p class="text-slate-600 dark:text-slate-400 mb-6">Our support engineers are ready to help you with any technical challenges or questions.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('kb.ticket.create') }}" class="bg-primary hover:bg-primary/90 text-white font-semibold py-3 px-8 rounded-lg transition-all shadow-lg shadow-primary/25">
                        Open a Support Ticket
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
