<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Knowledge Base Branding
    |--------------------------------------------------------------------------
    |
    | Configure the branding for your knowledge base pages including
    | company name, logo, colors, taglines, and footer content.
    |
    */

    'brand' => [
        // Company / product name shown in header, footer, and page titles
        'name' => env('KB_BRAND_NAME', 'Weeklify'),

        // Tagline shown in the footer beneath the logo
        'tagline' => env('KB_BRAND_TAGLINE', 'Helping you make the most of your experience with comprehensive documentation and support.'),

        // Copyright holder name (shown in footer)
        'copyright' => env('KB_BRAND_COPYRIGHT', 'Weeklify Inc.'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logo Configuration
    |--------------------------------------------------------------------------
    |
    | Set the logo displayed in the header and footer. You can use an
    | absolute URL, a path relative to the public directory, or leave
    | null to use the default bundled SVG logo.
    |
    | Supported formats: PNG, SVG, JPG, WEBP
    |
    */

    'logo' => [
        // Logo image URL or path (null = use default bundled logo)
        'url' => env('KB_LOGO_URL', null),

        // Alt text for the logo image
        'alt' => env('KB_LOGO_ALT', 'Knowledge Base'),

        // Header logo height class (Tailwind)
        'header_height' => 'h-8',

        // Footer logo height class (Tailwind)
        'footer_height' => 'h-6',
    ],

    /*
    |--------------------------------------------------------------------------
    | Colors
    |--------------------------------------------------------------------------
    |
    | Define the color palette for the knowledge base. These values are
    | injected into Tailwind CSS configuration at runtime.
    |
    | Use any valid CSS color value (hex, rgb, hsl, oklch, etc.)
    |
    */

    'colors' => [
        // Primary brand color (buttons, links, accents)
        'primary' => env('KB_COLOR_PRIMARY', '#0EA5E9'),

        // Light mode background
        'background_light' => env('KB_COLOR_BG_LIGHT', '#f6f6f8'),

        // Dark mode background
        'background_dark' => env('KB_COLOR_BG_DARK', '#101622'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Typography
    |--------------------------------------------------------------------------
    |
    | Font family used throughout the knowledge base pages.
    | Must be a Google Fonts family name or system font.
    |
    */

    'font' => [
        'family' => env('KB_FONT_FAMILY', 'Inter'),
        'google_fonts_url' => env('KB_FONT_URL', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    |
    | Search bar placeholder text and configuration.
    |
    */

    'search' => [
        'placeholder' => 'Search knowledge base...',
        'hero_placeholder' => 'Search for articles, keywords, or topics...',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hero Section (Landing Page)
    |--------------------------------------------------------------------------
    |
    | Customise the hero section on the KB landing page.
    |
    */

    'hero' => [
        'title' => 'How can we help you today?',
        'subtitle' => 'Search our knowledge base for tutorials, API references, and quick fixes to common issues.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Support / Ticket
    |--------------------------------------------------------------------------
    |
    | Configure the support button and contact details.
    |
    */

    'support' => [
        // Show the "Support" button in the header
        'enabled' => true,

        // Label for the support button
        'label' => 'Support',

        // Support email shown in articles footer
        'email' => env('KB_SUPPORT_EMAIL', 'support@weeklify.io'),

        // Support website URL
        'website' => env('KB_SUPPORT_WEBSITE', 'weeklify.cloud'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Footer Links
    |--------------------------------------------------------------------------
    |
    | Define the link columns shown in the KB footer.
    | Each group has a heading and an array of links.
    |
    */

    'footer' => [
        'columns' => [
            [
                'heading' => 'Resources',
                'links' => [
                    ['label' => 'Documentation', 'url' => '#'],
                    ['label' => 'Status Page', 'url' => '#'],
                    ['label' => 'Privacy Policy', 'url' => '#'],
                ],
            ],
            [
                'heading' => 'Company',
                'links' => [
                    ['label' => 'About', 'url' => '#'],
                    ['label' => 'Contact', 'url' => '#'],
                    ['label' => 'Support', 'url' => '#'],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the KB REST API endpoints. The API supports two
    | authentication methods: a static API key (via X-KB-API-Key header)
    | or Laravel Sanctum bearer tokens.
    |
    | Set KB_API_KEY in your .env to enable API key authentication.
    |
    */

    'api' => [
        // Static API key for simple integrations (null = disabled)
        'key' => env('KB_API_KEY', null),

        // Rate limit: max requests per minute per client
        'rate_limit' => env('KB_API_RATE_LIMIT', 60),
    ],

];
