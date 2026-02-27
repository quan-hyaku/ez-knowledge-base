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
                        "primary": "{{ config('kb.colors.primary') }}",
                        "background-light": "{{ config('kb.colors.background_light') }}",
                        "background-dark": "{{ config('kb.colors.background_dark') }}",
                    },
                    fontFamily: {
                        "display": ["{{ config('kb.font.family') }}"]
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
                <div class="relative hidden sm:block">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <span class="material-icons text-sm">search</span>
                    </span>
                    <form method="GET" action="{{ route('kb.search') }}" class="inline">
                        <input class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg text-sm w-64 focus:ring-2 focus:ring-primary" placeholder="{{ config('kb.search.placeholder') }}" type="text" name="q" value="{{ request('q') }}"/>
                    </form>
                </div>
                @if(config('kb.support.enabled'))
                <a href="{{ route('kb.ticket.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">{{ config('kb.support.label') }}</a>
                @endif
            </div>
        </div>
    </header>

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
                <h6 class="text-sm font-bold text-slate-900 dark:text-white mb-4">{{ $column['heading'] }}</h6>
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

    @stack('scripts')
</body>
</html>
