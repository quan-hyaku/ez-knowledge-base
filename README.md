# EzKnowledgeBase

A self-contained Laravel package that provides a fully themed knowledge base with categorised articles, full-text search, support tickets, and a REST API — all configurable via a single config file.

## Features

- Responsive landing page with hero search, category grid, and featured articles
- Category and article browsing with sidebar navigation
- Full-text fuzzy search powered by Laravel Scout + TNTSearch
- Markdown article bodies rendered to HTML with auto-generated table of contents
- Session-based unique view counting
- Article feedback (helpful yes/no)
- Support ticket submission form with Cloudflare Turnstile spam protection
- Email notifications on admin/staff ticket replies with direct email reply support
- Inbound email processing via Brevo webhook for customer replies
- Trait-based user integration (customer, staff, and admin roles)
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

This creates the `kb_categories`, `kb_articles`, `kb_tags`, `kb_article_tag`, `kb_tickets`, and `kb_ticket_replies` tables, along with a `user_id` foreign key on `kb_tickets`.

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

The host app should extend the package's base `KbArticle` model and add the `Searchable` trait:

```php
namespace App\Models;

use EzKnowledgeBase\Models\KbArticle as BaseKbArticle;
use Laravel\Scout\Searchable;

class KbArticle extends BaseKbArticle
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => strip_tags(str($this->body)->markdown()),
            'category_name' => $this->category?->name,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_published;
    }
}
```

Import existing articles into the search index:

```bash
php artisan scout:import "App\Models\KbArticle"
```

### 6. Set up user integration

Add the KB traits to your `User` model depending on the roles you need:

```php
namespace App\Models;

use EzKnowledgeBase\Traits\CanKbTicket;
use EzKnowledgeBase\Traits\ManageKbTicket;
use EzKnowledgeBase\Traits\AdminKbTicket;

class User extends Authenticatable
{
    use CanKbTicket;      // Customer: create and reply to own tickets
    use ManageKbTicket;   // Staff/Agent: reply to tickets as staff
    use AdminKbTicket;    // Admin: resolve, close, change status, admin checks
}
```

Configure admin email addresses in `config/kb.php`:

```php
'users' => [
    'model' => env('KB_USER_MODEL', 'App\\Models\\User'),
    'admins' => [
        'admin@example.com',
    ],
],
```

### 7. Set up email ticket replies (optional)

When enabled, admin/staff replies to support tickets automatically email the customer. Customers can reply directly to those emails, which are processed via Brevo's inbound parse webhook and stored as ticket replies.

Add to your `.env`:

```
KB_REPLY_ENABLED=true
KB_REPLY_DOMAIN=parse.yourdomain.com
KB_REPLY_FROM_ADDRESS=support@yourdomain.com
KB_REPLY_FROM_NAME="Your App Support"
KB_REPLY_WEBHOOK_SECRET=your-brevo-webhook-secret
```

**Brevo inbound parse setup:**

1. In your Brevo account, go to **Transactional → Settings → Inbound Parse**
2. Add a new inbound rule for your reply domain (e.g. `parse.yourdomain.com`)
3. Set the webhook URL to `https://yourdomain.com/webhook/kb/inbound`
4. Ensure your DNS MX record for the reply domain points to Brevo's inbound servers

**Webhook authentication:** The webhook is secured via the `X-Brevo-Secret` header. You **must** set `KB_REPLY_WEBHOOK_SECRET` — the webhook will return 403 if no secret is configured. The secret is only accepted via the `X-Brevo-Secret` header (query parameters are not supported).

**Brevo dependency:** The `getbrevo/brevo-php` package is suggested but not required. Install it if you need Brevo API integration beyond inbound webhooks:

```bash
composer require getbrevo/brevo-php
```

**CSRF exemption:** The webhook route is registered outside the `web` middleware group, but you should also add `webhook/kb/inbound` to your `VerifyCsrfToken` middleware's `$except` array if your app applies CSRF globally.

**How it works:**

1. Admin/staff replies to a ticket in the admin panel
2. The `KbTicketReplied` event fires, triggering the `SendTicketReplyNotification` queued listener
3. An email is sent to the customer with a `Reply-To` address containing an HMAC-signed token (e.g. `ticket+{token}@parse.yourdomain.com`)
4. When the customer replies, Brevo's inbound parse POSTs the email to your webhook
5. The webhook verifies the token (valid for 30 days by default), validates the sender email matches the ticket, checks spam score, sanitizes the HTML body, and stores the reply

The feature degrades gracefully — when `KB_REPLY_ENABLED=false` (the default), admin replies work normally with no emails sent.

### 8. Set up Cloudflare Turnstile (optional but recommended)

The ticket submission form supports Cloudflare Turnstile for spam protection. Add your Turnstile keys to `config/services.php`:

```php
'turnstile' => [
    'site_key'   => env('TURNSTILE_SITE_KEY'),
    'secret_key' => env('TURNSTILE_SECRET_KEY'),
],
```

And in your `.env`:

```
TURNSTILE_SITE_KEY=your-site-key
TURNSTILE_SECRET_KEY=your-secret-key
```

When configured, the Turnstile widget is automatically rendered on the ticket form and verified server-side on submission. When not configured, the form works without it.

## User Traits

The package provides three traits for integrating the ticket system with your User model:

### CanKbTicket (Customer)

| Method | Description |
|--------|-------------|
| `kbTickets()` | `HasMany` relationship to the user's tickets |
| `createKbTicket(array $data)` | Create a ticket, auto-fills name/email from user |
| `replyToKbTicket(KbTicket $ticket, string $body)` | Reply to own ticket (guards ownership) |
| `ownsKbTicket(KbTicket $ticket)` | Check if user owns the ticket |

### ManageKbTicket (Staff/Agent)

| Method | Description |
|--------|-------------|
| `replyToKbTicketAsStaff(KbTicket $ticket, string $body)` | Reply as staff, auto-transitions open → in_progress, fires `KbTicketReplied` event |

### AdminKbTicket (Admin)

| Method | Description |
|--------|-------------|
| `replyToKbTicketAsAdmin(KbTicket $ticket, string $body)` | Reply as admin, auto-transitions open → in_progress |
| `changeKbTicketStatus(KbTicket $ticket, string $status)` | Validate and update ticket status |
| `resolveKbTicket(KbTicket $ticket)` | Convenience wrapper to set status to resolved |
| `isKbAdmin()` | Check if user's email is in `config('kb.users.admins')` |

### Ticket Submission (Guests & Authenticated Users)

The ticket form supports both guest and authenticated submissions:

- **Guest**: must provide name and email in the form
- **Authenticated user**: name and email are auto-filled from the user model, and the ticket is linked via `user_id`

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
| POST | `/webhook/kb/inbound` | `kb.webhook.inbound` | Brevo inbound email webhook |

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
| `KB_USER_MODEL` | `App\Models\User` | Fully-qualified User model class |
| `KB_REPLY_ENABLED` | `false` | Enable outbound ticket reply emails |
| `KB_REPLY_DOMAIN` | _(empty)_ | Domain for reply-to addresses (e.g. `parse.yourdomain.com`) |
| `KB_REPLY_FROM_ADDRESS` | `noreply@weeklify.io` | From address for ticket reply emails |
| `KB_REPLY_FROM_NAME` | `Weeklify Support` | From name for ticket reply emails |
| `KB_REPLY_TOKEN_SECRET` | `null` | HMAC secret for reply tokens (falls back to `APP_KEY`) |
| `KB_REPLY_WEBHOOK_SECRET` | `null` | Secret for Brevo inbound webhook verification (**required** — webhook returns 403 if unset) |
| `KB_REPLY_TOKEN_TTL` | `2592000` | Reply token expiry in seconds (default 30 days) |
| `KB_BRAND_ADDRESS` | _(empty)_ | Physical address shown in email footer (hidden when empty) |
| `TURNSTILE_SITE_KEY` | `null` | Cloudflare Turnstile site key |
| `TURNSTILE_SECRET_KEY` | `null` | Cloudflare Turnstile secret key |

### Config Sections

The `config/kb.php` file is organised into these sections:

- **brand** — name, tagline, copyright, address
- **logo** — url, alt text, height classes
- **colors** — primary, background light/dark
- **font** — family, Google Fonts URL
- **search** — placeholder text
- **hero** — landing page title and subtitle
- **support** — enabled toggle, label, email, website
- **footer** — configurable link columns
- **api** — key, rate limit
- **reply** — email reply feature: enabled toggle, domain, from address/name, token/webhook secrets, spam threshold, token TTL
- **users** — user model class, admin email list

## Package Structure

```
EzKnowledgeBase/
├── composer.json
├── config/
│   └── kb.php                              # All branding and feature configuration
├── database/
│   └── migrations/                         # Category, article, tag, ticket, reply tables
├── public/
│   └── KB-logo.png                         # Default bundled logo
├── resources/
│   └── views/
│       ├── layout.blade.php                # Base layout (header, footer, Tailwind)
│       ├── landing.blade.php               # Home page
│       ├── categories.blade.php            # All categories
│       ├── category.blade.php              # Single category
│       ├── article.blade.php               # Single article
│       ├── search.blade.php                # Search results
│       ├── ticket.blade.php               # Support ticket form (with Turnstile widget + script)
│       └── emails/
│           └── ticket-reply.blade.php     # Ticket reply notification email
└── src/
    ├── EzKnowledgeBaseServiceProvider.php  # Service provider (config, routes, middleware, cache, events)
    ├── Events/
    │   └── KbTicketReplied.php            # Fired when admin/staff replies to a ticket
    ├── Listeners/
    │   └── SendTicketReplyNotification.php # Queued listener that sends reply email
    ├── Mail/
    │   └── KbTicketReplyMail.php          # Mailable for ticket reply notifications
    ├── Support/
    │   └── TicketToken.php                # HMAC token generation/verification for reply-to
    ├── Models/
    │   ├── KbArticle.php                  # Article model (without Scout)
    │   ├── KbCategory.php                 # Category model
    │   ├── KbTag.php                      # Tag model
    │   ├── KbTicket.php                   # Ticket model (with user relationship)
    │   └── KbTicketReply.php              # Ticket reply model
    ├── Traits/
    │   ├── CanKbTicket.php                # Customer trait
    │   ├── ManageKbTicket.php             # Staff/Agent trait
    │   └── AdminKbTicket.php              # Admin trait
    ├── Http/
    │   ├── Controllers/
    │   │   ├── ApiController.php           # REST API endpoints
    │   │   ├── KnowledgeBaseController.php # Web pages (landing, category, article)
    │   │   ├── SearchController.php        # Web search
    │   │   ├── TicketController.php        # Support ticket form (with Turnstile verification)
    │   │   └── InboundWebhookController.php # Brevo inbound email webhook
    │   └── Middleware/
    │       ├── ApiAuthenticate.php         # Dual auth: API key or Sanctum
    │       ├── TrackArticleView.php        # Session-based unique view counting
    │       └── VerifyBrevoWebhook.php      # Brevo webhook secret verification
    └── routes/
        ├── api.php                         # API route definitions
        └── web.php                         # Web route definitions
```

## Models

The package provides five Eloquent models in the `EzKnowledgeBase\Models` namespace:

- **KbCategory** — `kb_categories` table. Has many articles. Supports `is_active` flag and `sort_order`.
- **KbArticle** — `kb_articles` table. Belongs to a category, has many tags. Supports `is_published`, `is_featured`, view counting, and helpfulness votes. The host app can extend this model to add Laravel Scout.
- **KbTag** — `kb_tags` table. Many-to-many with articles via `kb_article_tag` pivot.
- **KbTicket** — `kb_tickets` table. Stores support ticket submissions. Optionally linked to a user via `user_id`.
- **KbTicketReply** — `kb_ticket_replies` table. Stores replies to tickets with `is_admin` flag and optional `user_id`.

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
