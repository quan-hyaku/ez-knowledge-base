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
                @if($article->read_time_minutes)
                <span class="flex items-center gap-2">
                    <span class="material-icons text-base" aria-hidden="true">schedule</span>
                    {{ $article->read_time_minutes }} min read
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
                @if($prevArticle)
                <a href="{{ route('kb.article', [$category->slug, $prevArticle->slug]) }}" class="group flex items-center gap-3 text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                    <span class="material-icons text-xl" aria-hidden="true">arrow_back</span>
                    <span>
                        <span class="block text-xs uppercase tracking-wider text-slate-400 mb-1">Previous</span>
                        <span class="block text-sm font-medium text-slate-900 dark:text-white group-hover:text-primary transition-colors">{{ $prevArticle->title }}</span>
                    </span>
                </a>
                @endif
            </div>
            <div>
                @if($nextArticle)
                <a href="{{ route('kb.article', [$category->slug, $nextArticle->slug]) }}" class="group flex items-center gap-3 text-slate-600 dark:text-slate-400 hover:text-primary transition-colors text-right">
                    <span>
                        <span class="block text-xs uppercase tracking-wider text-slate-400 mb-1">Next</span>
                        <span class="block text-sm font-medium text-slate-900 dark:text-white group-hover:text-primary transition-colors">{{ $nextArticle->title }}</span>
                    </span>
                    <span class="material-icons text-xl" aria-hidden="true">arrow_forward</span>
                </a>
                @endif
            </div>
        </nav>

        <!-- Feedback Widget -->
        <div id="feedback-widget" class="mt-12 bg-slate-100 dark:bg-slate-800/40 rounded-xl p-8 text-center">
            <div id="feedback-prompt">
                <h2 class="font-bold text-lg mb-6 text-slate-900 dark:text-white">Was this article helpful?</h2>
                <div class="flex justify-center gap-4">
                    <button id="btn-yes" onclick="submitFeedback('yes')" class="flex items-center gap-2 px-6 py-2 rounded-full border border-slate-300 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 hover:text-primary hover:border-primary transition-all">
                        <span class="material-icons" aria-hidden="true">thumb_up</span>
                        <span id="yes-count" aria-live="polite">Yes ({{ $article->helpful_yes_count }})</span>
                    </button>
                    <button id="btn-no" onclick="submitFeedback('no')" class="flex items-center gap-2 px-6 py-2 rounded-full border border-slate-300 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 hover:text-red-500 hover:border-red-500 transition-all">
                        <span class="material-icons" aria-hidden="true">thumb_down</span>
                        <span id="no-count" aria-live="polite">No ({{ $article->helpful_no_count }})</span>
                    </button>
                </div>
            </div>
            <div id="feedback-thanks" class="hidden" role="status" aria-live="polite" tabindex="-1">
                <div class="flex items-center justify-center gap-3 text-primary">
                    <span class="material-icons text-3xl" aria-hidden="true">check_circle</span>
                    <span class="font-semibold text-lg">Thanks for your feedback!</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile TOC Toggle -->
    @if(count($toc) > 0)
    <div class="lg:hidden fixed bottom-6 right-6 z-40">
        <button
            id="mobile-toc-toggle"
            aria-expanded="false"
            aria-controls="mobile-toc-panel"
            class="flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-full shadow-lg hover:bg-primary/90 transition-colors focus:outline-none focus:ring-2 focus:ring-primary/50"
        >
            <span class="material-icons text-sm" aria-hidden="true">toc</span>
            <span class="text-sm font-medium">On this page</span>
        </button>
    </div>

    <!-- Mobile TOC Overlay -->
    <div id="mobile-toc-backdrop" class="lg:hidden fixed inset-0 bg-black/50 z-40 hidden" aria-hidden="true"></div>
    <div
        id="mobile-toc-panel"
        role="dialog"
        aria-modal="true"
        aria-label="Table of contents"
        class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-slate-900 rounded-t-2xl shadow-2xl transform translate-y-full transition-transform duration-300 max-h-[70vh] flex flex-col"
    >
        <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">On this page</h2>
            <button id="mobile-toc-close" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" aria-label="Close table of contents">
                <span class="material-icons" aria-hidden="true">close</span>
            </button>
        </div>
        <nav aria-label="Table of contents" class="overflow-y-auto p-4">
            <ul class="space-y-3 text-sm">
                @foreach($toc as $heading)
                <li>
                    <a class="mobile-toc-link block py-1.5 px-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-primary hover:bg-primary/5 transition-colors" href="#{{ $heading['id'] }}" data-heading-id="{{ $heading['id'] }}">{{ $heading['text'] }}</a>
                </li>
                @endforeach
            </ul>
        </nav>
    </div>
    @endif

    <!-- RIGHT SIDEBAR - Table of Contents -->
    <aside aria-label="Table of contents" class="hidden lg:block lg:col-span-3 2xl:col-span-2 h-[calc(100vh-8rem)] sticky top-24 overflow-y-auto pl-4 border-l border-slate-200 dark:border-slate-800">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-6">On this page</h2>
        <nav aria-label="Table of contents">
            <ul class="space-y-4 text-sm">
                @foreach($toc as $index => $heading)
                <li><a class="toc-link {{ $index === 0 ? 'text-primary font-medium flex items-center gap-2 border-l-2 border-primary pl-4 -ml-[17px]' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors pl-4' }}" href="#{{ $heading['id'] }}" data-heading-id="{{ $heading['id'] }}">{{ $heading['text'] }}</a></li>
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

    function showThanks() {
        document.getElementById('feedback-prompt').classList.add('hidden');
        var thanks = document.getElementById('feedback-thanks');
        thanks.classList.remove('hidden');
        thanks.focus();
    }
})();

// Mobile TOC toggle
(function() {
    var toggle = document.getElementById('mobile-toc-toggle');
    var panel = document.getElementById('mobile-toc-panel');
    var backdrop = document.getElementById('mobile-toc-backdrop');
    var closeBtn = document.getElementById('mobile-toc-close');
    if (!toggle || !panel) return;

    function openToc() {
        backdrop.classList.remove('hidden');
        panel.classList.remove('translate-y-full');
        panel.classList.add('translate-y-0');
        toggle.setAttribute('aria-expanded', 'true');
        closeBtn.focus();
    }

    function closeToc() {
        panel.classList.remove('translate-y-0');
        panel.classList.add('translate-y-full');
        backdrop.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
    }

    toggle.addEventListener('click', openToc);
    closeBtn.addEventListener('click', closeToc);
    backdrop.addEventListener('click', closeToc);

    panel.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeToc();
    });

    // Close TOC when a link is clicked
    var mobileLinks = panel.querySelectorAll('.mobile-toc-link');
    mobileLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            closeToc();
        });
    });
})();

// TOC active state via IntersectionObserver
(function() {
    var tocLinks = document.querySelectorAll('.toc-link');
    var mobileTocLinks = document.querySelectorAll('.mobile-toc-link');
    var allLinks = Array.prototype.slice.call(tocLinks).concat(Array.prototype.slice.call(mobileTocLinks));
    if (allLinks.length === 0) return;

    var headingIds = [];
    allLinks.forEach(function(link) {
        var id = link.getAttribute('data-heading-id');
        if (id && headingIds.indexOf(id) === -1) headingIds.push(id);
    });

    var activeId = headingIds[0] || null;

    function setActive(id) {
        if (activeId === id) return;
        activeId = id;

        tocLinks.forEach(function(link) {
            var isActive = link.getAttribute('data-heading-id') === id;
            if (isActive) {
                link.className = 'toc-link text-primary font-medium flex items-center gap-2 border-l-2 border-primary pl-4 -ml-[17px]';
                link.setAttribute('aria-current', 'true');
            } else {
                link.className = 'toc-link text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors pl-4';
                link.removeAttribute('aria-current');
            }
        });

        mobileTocLinks.forEach(function(link) {
            var isActive = link.getAttribute('data-heading-id') === id;
            if (isActive) {
                link.className = 'mobile-toc-link block py-1.5 px-3 rounded-lg text-primary font-medium bg-primary/10 transition-colors';
                link.setAttribute('aria-current', 'true');
            } else {
                link.className = 'mobile-toc-link block py-1.5 px-3 rounded-lg text-slate-600 dark:text-slate-400 hover:text-primary hover:bg-primary/5 transition-colors';
                link.removeAttribute('aria-current');
            }
        });
    }

    // Set initial active
    setActive(headingIds[0]);

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                setActive(entry.target.id);
            }
        });
    }, { rootMargin: '-80px 0px -70% 0px', threshold: 0 });

    headingIds.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) observer.observe(el);
    });
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
            showThanks();
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
                showThanks();
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
