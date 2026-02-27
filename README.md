# EzKnowledgeBase

A self-contained Laravel package that provides a fully themed knowledge base with categorised articles, full-text search, support tickets, and a REST API — all configurable via a single config file.

## Features

- Responsive landing page with hero search, category grid, and featured articles
- Category and article browsing with sidebar navigation
- Full-text fuzzy search powered by Laravel Scout + TNTSearch
- Markdown article bodies rendered to HTML with auto-generated table of contents
- Session-based unique view counting
- Article feedback (helpful yes/no)
- Support ticket submission form
- REST API with dual authentication (API key or Sanctum)
- Fully configurable branding: logo, colours, fonts, copy, footer links
- Built-in caching with automatic invalidation
- Dark mode support

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12
- Laravel Scout + TNTSearch driver (for search)
- Laravel Sanctum (optional, for token-based API auth)

## Installation

### 1. Install via Composer

```bash
composer require weeklify/ez-knowledge-base
```

The service provider is auto-discovered via Laravel's package discovery. If you need to register it manually, add to `config/app.php` providers array:

```php
EzKnowledgeBase\EzKnowledgeBaseServiceProvider::class,
```

### 2. Publish config and assets

```bash
# Publish the config file to config/kb.php
php artisan vendor:publish --tag=kb-config

# Publish the default logo to public/vendor/kb/
php artisan vendor:publish --tag=kb-assets
```

### 3. Run migrations

```bash
php artisan migrate
```

This creates the `kb_categories`, `kb_articles`, `kb_tags`, `kb_article_tag`, and `kb_tickets` tables.

### 4. Seed sample data (optional)

```bash
php artisan db:seed --class=KbSeeder
```

### 5. Set up search (optional but recommended)

```bash
composer require laravel/scout teamtnt/laravel-scout-tntsearch-driver
```

Add to `.env`:

```
SCOUT_DRIVER=tntsearch
```

Import existing articles into the search index:

```bash
php artisan scout:import "App\Models\KbArticle"
```

## Web Routes

All web routes are prefixed with `/help-center` and use the `web` middleware group.

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/help-center` | `kb.landing` | Landing page with categories + featured articles |
| GET | `/help-center/categories` | `kb.categories` | All categories with top articles |
| GET | `/help-center/category/{slug}` | `kb.category` | Single category with paginated articles |
| GET | `/help-center/{category}/{article}` | `kb.article` | Single article (tracks views) |
| GET | `/help-center/search` | `kb.search` | Search results page |
| GET | `/help-center/ticket` | `kb.ticket.create` | Support ticket form |
| POST | `/help-center/ticket` | `kb.ticket.store` | Submit support ticket |
| POST | `/help-center/article/{id}/feedback` | `kb.article.feedback` | Article helpfulness vote |

## API Endpoints

All API routes are prefixed with `/api/kb`, rate-limited, and require authentication.

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/api/kb` | `kb.api.home` | Categories with counts + featured articles |
| GET | `/api/kb/categories/{slug}` | `kb.api.category` | Category detail + paginated articles |
| GET | `/api/kb/categories/{slug}/{article}` | `kb.api.article` | Full article with HTML body + TOC |
| GET | `/api/kb/search?q=&category=` | `kb.api.search` | Full-text search with optional category filter |

### API Authentication

The API accepts **either** of these authentication methods:

**Option 1 — Static API Key** (simplest)

Set a key in `.env`:

```
KB_API_KEY=your-secret-key-here
```

Then pass it via header:

```bash
curl -H "X-KB-API-Key: your-secret-key-here" https://example.com/api/kb
```

**Option 2 — Sanctum Bearer Token**

Use a standard Sanctum personal access token:

```bash
curl -H "Authorization: Bearer {your-sanctum-token}" https://example.com/api/kb
```

### Example API Responses

**GET /api/kb**

```json
{
  "data": {
    "categories": [
      {
        "id": 1,
        "name": "Getting Started",
        "slug": "getting-started",
        "description": "Learn the basics...",
        "icon": "rocket_launch",
        "sort_order": 0,
        "articles_count": 9
      }
    ],
    "featured_articles": [
      {
        "id": 1,
        "title": "Creating Your Weeklify Account",
        "slug": "creating-your-weeklify-account",
        "excerpt": "Learn how to create your account...",
        "read_time_minutes": 5,
        "is_featured": true,
        "view_count": 42,
        "published_at": null,
        "category": {
          "id": 1,
          "name": "Getting Started",
          "slug": "getting-started"
        }
      }
    ]
  }
}
```

**GET /api/kb/categories/{slug}/{article}**

```json
{
  "data": {
    "article": {
      "id": 1,
      "title": "Creating Your First Place",
      "slug": "creating-your-first-place",
      "excerpt": "Step-by-step guide...",
      "body_markdown": "# Creating Your First Place\n\n...",
      "body_html": "<h1 id=\"creating-your-first-place\">Creating Your First Place</h1>...",
      "toc": [
        { "text": "Opening the Create Place Wizard", "id": "opening-the-create-place-wizard" },
        { "text": "Step 1: Select Your Service Type", "id": "step-1-select-your-service-type" }
      ],
      "read_time_minutes": 8,
      "view_count": 15,
      "helpful_yes_count": 7,
      "helpful_no_count": 1,
      "published_at": null,
      "created_at": "2026-02-14T12:00:00.000000Z",
      "updated_at": "2026-02-14T12:00:00.000000Z"
    },
    "category": {
      "id": 1,
      "name": "Getting Started",
      "slug": "getting-started"
    }
  }
}
```

## Configuration

After publishing (`php artisan vendor:publish --tag=kb-config`), edit `config/kb.php` to customise your knowledge base.

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `KB_BRAND_NAME` | `Weeklify` | Brand name in header, footer, page titles |
| `KB_BRAND_TAGLINE` | `Helping you make the most...` | Footer tagline |
| `KB_BRAND_COPYRIGHT` | `Weeklify Inc.` | Copyright holder |
| `KB_LOGO_URL` | `null` | Custom logo URL (null = bundled logo) |
| `KB_LOGO_ALT` | `Knowledge Base` | Logo alt text |
| `KB_COLOR_PRIMARY` | `#0EA5E9` | Primary brand colour |
| `KB_COLOR_BG_LIGHT` | `#f6f6f8` | Light mode background |
| `KB_COLOR_BG_DARK` | `#101622` | Dark mode background |
| `KB_FONT_FAMILY` | `Inter` | Google Font family name |
| `KB_FONT_URL` | Google Fonts URL | Font stylesheet URL |
| `KB_SUPPORT_EMAIL` | `support@weeklify.io` | Support contact email |
| `KB_SUPPORT_WEBSITE` | `weeklify.cloud` | Support website |
| `KB_API_KEY` | `null` | Static API key (null = disabled) |
| `KB_API_RATE_LIMIT` | `60` | API requests per minute |

### Config Sections

The `config/kb.php` file is organised into these sections:

- **brand** — name, tagline, copyright
- **logo** — url, alt text, height classes
- **colors** — primary, background light/dark
- **font** — family, Google Fonts URL
- **search** — placeholder text
- **hero** — landing page title and subtitle
- **support** — enabled toggle, label, email, website
- **footer** — configurable link columns
- **api** — key, rate limit

## Package Structure

```
EzKnowledgeBase/
├── composer.json
├── config/
│   └── kb.php                              # All branding and feature configuration
├── public/
│   └── KB-logo.png                         # Default bundled logo
├── resources/
│   └── views/
│       ├── layout.blade.php                # Base layout (header, footer, Tailwind config)
│       ├── landing.blade.php               # Home page
│       ├── categories.blade.php            # All categories
│       ├── category.blade.php              # Single category
│       ├── article.blade.php               # Single article
│       ├── search.blade.php                # Search results
│       └── ticket.blade.php               # Support ticket form
└── src/
    ├── EzKnowledgeBaseServiceProvider.php  # Service provider (config, routes, middleware, cache)
    ├── Http/
    │   ├── Controllers/
    │   │   ├── ApiController.php           # REST API endpoints
    │   │   ├── KnowledgeBaseController.php # Web pages (landing, category, article)
    │   │   ├── SearchController.php        # Web search
    │   │   └── TicketController.php        # Support ticket form
    │   └── Middleware/
    │       ├── ApiAuthenticate.php         # Dual auth: API key or Sanctum
    │       └── TrackArticleView.php        # Session-based unique view counting
    └── routes/
        ├── api.php                         # API route definitions
        └── web.php                         # Web route definitions
```

## Models

The package uses four Eloquent models (defined in `app/Models/`):

- **KbCategory** — `kb_categories` table. Has many articles. Supports `is_active` flag and `sort_order`.
- **KbArticle** — `kb_articles` table. Belongs to a category, has many tags. Uses Laravel Scout for full-text search. Supports `is_published`, `is_featured`, view counting, and helpfulness votes.
- **KbTag** — `kb_tags` table. Many-to-many with articles via `kb_article_tag` pivot.
- **KbTicket** — `kb_tickets` table. Stores support ticket submissions.

## Caching

The package caches expensive queries with automatic invalidation:

| Cache Key | TTL | Invalidated On |
|-----------|-----|----------------|
| `kb_categories_with_counts` | 1 hour | Category or article save/delete |
| `kb_all_categories_with_top_articles` | 1 hour | Category or article save/delete |
| `kb_featured_articles` | 1 hour | Category or article save/delete |
| `kb_article_{slug}` | 30 min | That article's save/delete |

Cache invalidation is handled via Eloquent model event listeners registered in the service provider.

## Customising Views

To override any Blade view, publish them to your app:

```bash
php artisan vendor:publish --tag=kb-views
```

Views will be copied to `resources/views/vendor/kb/` where you can edit them freely. The package will use your custom views over its built-in ones.

## Adding Articles

Articles are stored in `database/seeders/data/kb/` as PHP arrays with markdown bodies using nowdoc syntax:

```php
[
    'title' => 'My New Article',
    'slug' => 'my-new-article',
    'excerpt' => 'A brief description of this article.',
    'body' => <<<'MD'
# My New Article

Your markdown content here...
MD,
    'is_published' => true,
    'is_featured' => false,
    'read_time_minutes' => 5,
    'sort_order' => 1,
],
```

After adding articles to the seeder data files, run:

```bash
php artisan db:seed --class=KbSeeder
php artisan scout:import "App\Models\KbArticle"
```

Articles can also be managed through the Filament admin panel.
