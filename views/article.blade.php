@extends('kb::layout')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
    <!-- LEFT SIDEBAR - Category Navigation -->
    <aside aria-label="Category navigation" class="hidden lg:block lg:col-span-3 2xl:col-span-2 h-[calc(100vh-8rem)] sticky top-24 overflow-y-auto pr-4">
        <div class="space-y-8">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">{{ $category->name }}</h2>
                <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
                    @foreach($sidebarArticles as $sideArticle)
                    <li>
                        <a class="{{ $sideArticle->id === $article->id ? 'text-primary font-semibold' : 'hover:text-primary transition-colors' }}" href="{{ route('kb.article', [$category->slug, $sideArticle->slug]) }}">{{ $sideArticle->title }}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="lg:col-span-6 2xl:col-span-8">
        <!-- Breadcrumbs -->
        <nav aria-label="Breadcrumb" class="mb-8 text-sm text-slate-600 dark:text-slate-400">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('kb.landing') }}" class="hover:text-primary transition-colors">Home</a></li>
                <li><span class="text-slate-400" aria-hidden="true">/</span></li>
                <li><a href="{{ route('kb.category', $category->slug) }}" class="hover:text-primary transition-colors">{{ $category->name }}</a></li>
                <li><span class="text-slate-400" aria-hidden="true">/</span></li>
                <li aria-current="page"><span class="text-slate-900 dark:text-white font-medium">{{ $article->title }}</span></li>
            </ol>
        </nav>

        <!-- Article Header -->
        <header class="mb-8 pb-8 border-b border-slate-200 dark:border-slate-800">
            <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-4">{{ $article->title }}</h1>
            <div class="flex items-center gap-4 text-sm text-slate-600 dark:text-slate-400">
                @if($article->read_time)
                <span class="flex items-center gap-2">
                    <span class="material-icons text-base" aria-hidden="true">schedule</span>
                    {{ $article->read_time }} min read
                </span>
                @endif
                @if($article->updated_at)
                <span class="flex items-center gap-2">
                    <span class="material-icons text-base" aria-hidden="true">update</span>
                    Updated {{ $article->updated_at->format('M d, Y') }}
                </span>
                @endif
            </div>
        </header>

        <!-- Article Body -->
        <article class="prose prose-lg prose-slate dark:prose-invert max-w-none mb-12 prose-headings:scroll-mt-24 prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-img:rounded-xl prose-pre:bg-slate-900 prose-code:text-primary prose-code:before:content-none prose-code:after:content-none">
            {!! $parsedBody !!}
        </article>

        <!-- Prev/Next Navigation -->
        <nav aria-label="Article navigation" class="flex justify-between items-center my-12 pt-8 border-t border-slate-200 dark:border-slate-800">
            <div>
                <!-- Previous article link can go here -->
            </div>
            <div>
                <!-- Next article link can go here -->
            </div>
        </nav>

        <!-- Feedback Widget -->
        <div id="feedback-widget" class="mt-12 bg-slate-100 dark:bg-slate-800/40 rounded-xl p-8 text-center">
            <div id="feedback-prompt">
                <h2 class="font-bold text-lg mb-6 text-slate-900 dark:text-white">Was this article helpful?</h2>
                <div class="flex justify-center gap-4">
                    <button id="btn-yes" onclick="submitFeedback('yes')" class="flex items-center gap-2 px-6 py-2 rounded-full border border-slate-300 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 hover:text-primary hover:border-primary transition-all">
                        <span class="material-icons" aria-hidden="true">thumb_up</span>
                        <span id="yes-count">Yes ({{ $article->helpful_yes_count }})</span>
                    </button>
                    <button id="btn-no" onclick="submitFeedback('no')" class="flex items-center gap-2 px-6 py-2 rounded-full border border-slate-300 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 hover:text-red-500 hover:border-red-500 transition-all">
                        <span class="material-icons" aria-hidden="true">thumb_down</span>
                        <span id="no-count">No ({{ $article->helpful_no_count }})</span>
                    </button>
                </div>
            </div>
            <div id="feedback-thanks" class="hidden">
                <div class="flex items-center justify-center gap-3 text-primary">
                    <span class="material-icons text-3xl" aria-hidden="true">check_circle</span>
                    <span class="font-semibold text-lg">Thanks for your feedback!</span>
                </div>
            </div>
        </div>
    </main>

    <!-- RIGHT SIDEBAR - Table of Contents -->
    <aside aria-label="Table of contents" class="hidden lg:block lg:col-span-3 2xl:col-span-2 h-[calc(100vh-8rem)] sticky top-24 overflow-y-auto pl-4 border-l border-slate-200 dark:border-slate-800">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-6">On this page</h2>
        <nav aria-label="Table of contents">
            <ul class="space-y-4 text-sm">
                @foreach($toc as $index => $heading)
                <li><a class="{{ $index === 0 ? 'text-primary font-medium flex items-center gap-2 border-l-2 border-primary pl-4 -ml-[17px]' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors pl-4' }}" href="#{{ $heading['id'] }}">{{ $heading['text'] }}</a></li>
                @endforeach
            </ul>
        </nav>
        <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800">
            <div class="bg-primary rounded-xl p-6 text-white relative overflow-hidden group cursor-pointer">
                <div class="relative z-10">
                    <h3 class="font-bold mb-2">Need Help?</h3>
                    <p class="text-xs text-white/80 mb-4 leading-relaxed">Can't find what you're looking for? Talk to our support team.</p>
                    <a href="{{ route('kb.ticket.create') }}" class="bg-white text-primary px-4 py-2 rounded-lg text-xs font-bold hover:bg-background-light transition-colors inline-block">Contact Support</a>
                </div>
                <span class="material-icons absolute -right-4 -bottom-4 text-white/10 text-8xl transform -rotate-12 group-hover:scale-110 transition-transform" aria-hidden="true">support_agent</span>
            </div>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var voted = localStorage.getItem('kb_feedback_{{ $article->id }}');
    if (voted) {
        document.getElementById('feedback-prompt').classList.add('hidden');
        document.getElementById('feedback-thanks').classList.remove('hidden');
    }
})();

function submitFeedback(vote) {
    if (localStorage.getItem('kb_feedback_{{ $article->id }}')) return;

    var btnYes = document.getElementById('btn-yes');
    var btnNo = document.getElementById('btn-no');
    btnYes.disabled = true;
    btnNo.disabled = true;

    fetch('{{ route("kb.article.feedback", $article->id) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ vote: vote })
    })
    .then(function(r) { return r.json().then(function(data) { return { status: r.status, data: data }; }); })
    .then(function(result) {
        var data = result.data;

        if (result.status === 409) {
            // Already voted server-side — sync localStorage and show thanks
            localStorage.setItem('kb_feedback_{{ $article->id }}', 'voted');
            document.getElementById('feedback-prompt').classList.add('hidden');
            document.getElementById('feedback-thanks').classList.remove('hidden');
            return;
        }

        if (data.success) {
            localStorage.setItem('kb_feedback_{{ $article->id }}', vote);
            document.getElementById('yes-count').textContent = 'Yes (' + data.helpful_yes_count + ')';
            document.getElementById('no-count').textContent = 'No (' + data.helpful_no_count + ')';

            if (vote === 'yes') {
                btnYes.classList.add('bg-primary', 'text-white', 'border-primary');
            } else {
                btnNo.classList.add('bg-red-500', 'text-white', 'border-red-500');
            }

            setTimeout(function() {
                document.getElementById('feedback-prompt').classList.add('hidden');
                document.getElementById('feedback-thanks').classList.remove('hidden');
            }, 800);
        }
    })
    .catch(function() {
        btnYes.disabled = false;
        btnNo.disabled = false;
    });
}
</script>
@endpush
