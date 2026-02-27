<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Knowledge Base') - {{ config('kb.brand.name') }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries,typography"></script>
    <link href="{{ config('kb.font.google_fonts_url') }}" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": @json(config('kb.colors.primary')),
                        "background-light": @json(config('kb.colors.background_light')),
                        "background-dark": @json(config('kb.colors.background_dark')),
                    },
                    fontFamily: {
                        "display": [@json(config('kb.font.family'))]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
    <style>
        body { font-family: '{{ config('kb.font.family') }}', sans-serif; }
        .search-highlight { background-color: {{ config('kb.colors.primary') }}1a; color: {{ config('kb.colors.primary') }}; padding: 0 2px; border-radius: 2px; font-weight: 500; }
    </style>
    @stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:bg-primary focus:text-white focus:rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50">Skip to main content</a>
    <!-- Header Navigation -->
    <header class="sticky top-0 z-50 w-full border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-background-dark/80 backdrop-blur-md">
        <div class="w-full px-6 lg:px-10 h-16 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a class="flex items-center gap-2 font-bold text-xl tracking-tight text-primary" href="{{ route('kb.landing') }}">
                    @if(config('kb.logo.url'))
                        <img src="{{ config('kb.logo.url') }}" alt="{{ config('kb.logo.alt') }}" class="{{ config('kb.logo.header_height') }} w-auto">
                    @else
                        <img src="{{ asset('vendor/kb/KB-logo.png') }}" alt="{{ config('kb.logo.alt') }}" class="{{ config('kb.logo.header_height') }} w-auto">
                    @endif
                </a>
                <nav aria-label="Main navigation" class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600 dark:text-slate-400">
                    <a class="hover:text-primary transition-colors" href="{{ route('kb.landing') }}">Home</a>
                    <a class="hover:text-primary transition-colors" href="{{ route('kb.categories') }}">Categories</a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <!-- Mobile menu button -->
                <button id="mobile-menu-btn" class="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/50" aria-expanded="false" aria-controls="mobile-menu" aria-label="Open navigation menu">
                    <span class="material-icons" aria-hidden="true">menu</span>
                </button>
                <div class="relative hidden sm:block">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <span class="material-icons text-sm" aria-hidden="true">search</span>
                    </span>
                    <form method="GET" action="{{ route('kb.search') }}" class="inline">
                        <label for="header-search" class="sr-only">Search knowledge base</label>
                        <input id="header-search" class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg text-sm w-64 focus:ring-2 focus:ring-primary" placeholder="{{ config('kb.search.placeholder') }}" type="text" name="q" value="{{ request('q') }}" aria-label="Search knowledge base"/>
                    </form>
                </div>
                @if(config('kb.support.enabled'))
                <a href="{{ route('kb.ticket.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">{{ config('kb.support.label') }}</a>
                @endif
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Menu -->
    <div id="mobile-menu-backdrop" class="fixed inset-0 z-40 bg-black/50 hidden" aria-hidden="true"></div>
    <nav id="mobile-menu" class="fixed top-0 right-0 z-50 h-full w-72 max-w-[80vw] bg-white dark:bg-slate-900 shadow-xl transform translate-x-full transition-transform duration-200 ease-in-out" aria-label="Mobile navigation" role="dialog" aria-modal="true">
        <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-800">
            <span class="font-bold text-lg text-primary">Menu</span>
            <button id="mobile-menu-close" class="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/50" aria-label="Close navigation menu">
                <span class="material-icons" aria-hidden="true">close</span>
            </button>
        </div>
        <div class="p-4 space-y-1">
            <a href="{{ route('kb.landing') }}" class="mobile-menu-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-medium">
                <span class="material-icons text-slate-400" aria-hidden="true">home</span>
                Home
            </a>
            <a href="{{ route('kb.categories') }}" class="mobile-menu-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-medium">
                <span class="material-icons text-slate-400" aria-hidden="true">category</span>
                Categories
            </a>
            @if(config('kb.support.enabled'))
            <a href="{{ route('kb.ticket.create') }}" class="mobile-menu-link flex items-center gap-3 px-3 py-3 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-medium">
                <span class="material-icons text-slate-400" aria-hidden="true">support_agent</span>
                {{ config('kb.support.label') }}
            </a>
            @endif
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            <form method="GET" action="{{ route('kb.search') }}">
                <label for="mobile-search" class="sr-only">Search knowledge base</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <span class="material-icons text-sm" aria-hidden="true">search</span>
                    </span>
                    <input id="mobile-search" class="mobile-menu-link w-full pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary" placeholder="{{ config('kb.search.placeholder') }}" type="text" name="q" value="{{ request('q') }}" aria-label="Search knowledge base"/>
                </div>
            </form>
        </div>
    </nav>

    <div id="main-content" class="w-full px-6 lg:px-10 py-8">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="mt-20 border-t border-slate-200 dark:border-slate-800 py-12 bg-white dark:bg-background-dark">
        <div class="w-full px-6 lg:px-10 grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="col-span-2">
                <div class="flex items-center gap-2 font-bold text-xl tracking-tight text-primary mb-4">
                    @if(config('kb.logo.url'))
                        <img src="{{ config('kb.logo.url') }}" alt="{{ config('kb.logo.alt') }}" class="{{ config('kb.logo.footer_height') }} w-auto">
                    @else
                        <img src="{{ asset('vendor/kb/KB-logo.png') }}" alt="{{ config('kb.logo.alt') }}" class="{{ config('kb.logo.footer_height') }} w-auto">
                    @endif
                </div>
                <p class="text-sm text-slate-500 max-w-xs">{{ config('kb.brand.tagline') }}</p>
            </div>
            @foreach(config('kb.footer.columns', []) as $column)
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-4">{{ $column['heading'] }}</h2>
                <ul class="space-y-2 text-sm text-slate-500">
                    @foreach($column['links'] as $link)
                    <li><a class="hover:text-primary" href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
        <div class="w-full px-6 lg:px-10 mt-12 pt-8 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ config('kb.brand.copyright') }}. All rights reserved.
        </div>
    </footer>

    <script>
    (function() {
        const menuBtn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        const backdrop = document.getElementById('mobile-menu-backdrop');
        const closeBtn = document.getElementById('mobile-menu-close');
        const menuLinks = menu.querySelectorAll('.mobile-menu-link');
        const focusableEls = [closeBtn, ...menuLinks];

        function openMenu() {
            menu.classList.remove('translate-x-full');
            menu.classList.add('translate-x-0');
            backdrop.classList.remove('hidden');
            menuBtn.setAttribute('aria-expanded', 'true');
            closeBtn.focus();
        }

        function closeMenu() {
            menu.classList.add('translate-x-full');
            menu.classList.remove('translate-x-0');
            backdrop.classList.add('hidden');
            menuBtn.setAttribute('aria-expanded', 'false');
            menuBtn.focus();
        }

        menuBtn.addEventListener('click', openMenu);
        closeBtn.addEventListener('click', closeMenu);
        backdrop.addEventListener('click', closeMenu);

        menuLinks.forEach(function(link) {
            if (link.tagName === 'A') {
                link.addEventListener('click', closeMenu);
            }
        });

        menu.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMenu();
                return;
            }
            if (e.key === 'Tab') {
                var first = focusableEls[0];
                var last = focusableEls[focusableEls.length - 1];
                if (e.shiftKey) {
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>
