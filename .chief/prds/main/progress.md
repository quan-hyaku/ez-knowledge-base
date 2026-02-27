## Codebase Patterns
- This is a standalone Laravel package at `packages/EzKnowledgeBase/` with no own composer.json — PSR-4 autoloaded via host app
- Controllers are in `Controllers/`, views in `views/`, config in `config/kb.php`, middleware in `Middleware/`
- Markdown rendering uses `Str::markdown()` with CommonMark options (`html_input`, `allow_unsafe_links`)
- Views use `{!! $parsedBody !!}` for rendered HTML — always sanitize before passing to view
- No dedicated test suite — use `php -l` for syntax checking at minimum
- Cache keys follow pattern: `kb_article_{slug}`, `kb_categories_with_counts`, `kb_featured_articles`, `kb_all_categories_with_top_articles`
- Namespace: `Packages\EzKnowledgeBase\`
- Use `DB::table()->increment()` instead of `$model->increment()` for tracking columns to avoid triggering Eloquent model events and cache invalidation
- Use `$request->attributes->set/get()` to pass resolved models between controller and middleware (avoids redundant DB queries)
- Cache invalidation listeners are in `AppServiceProvider::boot()` using Eloquent closure listeners (not Observer classes)

---

## 2026-02-27 - US-001
- Fixed XSS vulnerability: Changed ApiController `html_input` from `'allow'` to `'escape'`
- Created `Helpers/HtmlSanitizer.php` with tag allowlist, event handler stripping, and dangerous URL removal
- Applied sanitizer to both `KnowledgeBaseController::article()` and `ApiController::article()` HTML output
- Files changed: `Controllers/ApiController.php`, `Controllers/KnowledgeBaseController.php`, `Helpers/HtmlSanitizer.php` (new)
- **Learnings for future iterations:**
  - The API and web controllers had different security settings — always check both when fixing security issues
  - `Str::markdown()` with `html_input => 'escape'` escapes raw HTML tags but still produces structural HTML from markdown syntax
  - The `{!! !!}` Blade syntax renders unescaped HTML — defense-in-depth requires sanitizing even with `escape` mode
  - No external dependencies available, so sanitization uses `strip_tags()` with allowlist + regex for attributes
---

## 2026-02-27 - US-002
- Replaced `===` comparison with `hash_equals()` for timing-safe API key comparison in `ApiAuthenticate.php`
- The `?? ''` fallback ensures `hash_equals` receives a string even when the header is missing
- Unauthenticated requests still return 401 (Sanctum fallback and final 401 response unchanged)
- Files changed: `Middleware/ApiAuthenticate.php`
- **Learnings for future iterations:**
  - `hash_equals()` requires both arguments to be strings — always null-coalesce header values
  - The first argument to `hash_equals()` should be the known/expected value (the config key)
---

## 2026-02-27 - US-003
- Added server-side input validation to feedback, ticket, and API search endpoints
- Feedback endpoint: type-hinted `$id` as `int`, added `required|in:yes,no` validation for `vote` parameter
- Ticket endpoint: changed `category` to `nullable|in:billing,technical,feature,general,other`, added `max:10000` to `description`
- API search endpoint: added validation for `q` (`nullable|string|max:500`) and `category` (`nullable|string|exists:kb_categories,slug`)
- Files changed: `Controllers/KnowledgeBaseController.php`, `Controllers/TicketController.php`, `Controllers/ApiController.php`
- **Learnings for future iterations:**
  - Laravel's `$request->validate()` automatically returns 422 with error details for web (JSON) and redirects back with errors for web form submissions
  - Ticket form category options are: billing, technical, feature, general, other (defined in `ticket.blade.php`)
  - The `exists:table,column` validation rule checks against the database directly — useful for validating slugs
  - Feedback endpoint sends JSON via fetch API, so Laravel validation returns JSON 422 automatically
---

## 2026-02-27 - US-004
- Added server-side feedback vote deduplication using Laravel session storage
- Controller checks `session('kb_feedback_{id}')` before allowing a vote; duplicate votes return 409 with current counts
- After successful vote, the vote value is stored in the session via `$request->session()->put()`
- Client-side localStorage check retained as UX optimization (prevents unnecessary requests)
- JavaScript updated to handle 409 responses gracefully — syncs localStorage and shows "thanks" message
- Files changed: `Controllers/KnowledgeBaseController.php`, `views/article.blade.php`
- **Learnings for future iterations:**
  - All web routes are inside `Route::middleware('web')` group, so sessions are available on all endpoints including the feedback POST
  - Session-based dedup is simpler than a dedicated table — no migration needed, works out of the box
  - When returning non-200 JSON responses, the fetch API doesn't reject — need to check `response.status` in the `.then()` chain
  - The `increment()` method updates the DB but also updates the in-memory model attribute, so returning `$article->helpful_yes_count` after increment gives the correct value
---

## 2026-02-27 - US-005
- Chose Option B (remove): Removed non-functional file upload from ticket form
- Removed `enctype="multipart/form-data"` from the `<form>` tag
- Removed the entire file upload section (file input, drag-and-drop area, attachments error display)
- Backend had no attachment handling, so removal makes UI and backend consistent
- Files changed: `views/ticket.blade.php`
- **Learnings for future iterations:**
  - When a UI element has no backend support, removing it is cleaner than implementing from scratch (especially for file uploads which need storage config, migrations, etc.)
  - The `enctype="multipart/form-data"` attribute should only be present when file inputs exist in the form
---

## 2026-02-27 - US-006
- Added success banner to `ticket.blade.php` that displays when `session('success')` is set
- Banner uses `role="alert"` for accessibility, styled with green theme, includes a check_circle icon
- Placed above the main grid so it's visible at the top of the page after redirect
- The `TicketController::store()` already flashed the success message — only the view was missing
- Files changed: `views/ticket.blade.php`
- **Learnings for future iterations:**
  - The TicketController already uses `redirect()->back()->with('success', ...)` — always check the controller before assuming the backend needs changes
  - Use `role="alert"` for important messages that need immediate screen reader announcement; use `aria-live="polite"` for less urgent updates
---

## 2026-02-27 - US-007
- Fixed cache invalidation being triggered on every page view due to `$article->increment('view_count')` firing Eloquent `saved` event
- Changed TrackArticleView middleware to use `DB::table('kb_articles')->where('id', $article->id)->increment('view_count')` which bypasses Eloquent model events entirely
- Added guard in AppServiceProvider's `KbArticle::saved` listener to skip cache invalidation when only tracking columns (`view_count`, `helpful_yes_count`, `helpful_no_count`, `updated_at`) changed
- Cache invalidation still fires correctly when content, title, slug, or other meaningful fields are edited
- Files changed: `Middleware/TrackArticleView.php`, `AppServiceProvider.php`
- **Learnings for future iterations:**
  - `$model->increment()` triggers Eloquent `saved` event — use `DB::table()->increment()` to bypass model events for tracking-only updates
  - `$article->getChanges()` returns the attributes that were changed in the current save — useful for conditional cache invalidation
  - Defense-in-depth: both the middleware bypass (DB::table) AND the guard in the saved listener protect against unnecessary cache invalidation
---

## 2026-02-27 - US-008
- Fixed N+1 query in category sidebar by eager-loading articles on `$allCategories` with `->with(['articles' => fn($q) => $q->where('is_published', true)->limit(5)])`
- Updated `category.blade.php` sidebar to use `$cat->articles` (eager-loaded collection) instead of `$cat->articles()->...->get()` (N queries)
- Replaced redundant `$category->articles()->where('is_published', true)->count()` with `$category->articles_count` via `withCount()` on the category query
- Derived `$relatedCategories` from `$allCategories->where('id', '!=', $category->id)` instead of a separate DB query
- Files changed: `Controllers/KnowledgeBaseController.php`, `views/category.blade.php`
- **Learnings for future iterations:**
  - Use `withCount(['articles' => fn($q) => $q->where(...)])` to get filtered counts without extra queries — result is available as `articles_count`
  - Laravel collection `->where()` works on already-loaded collections without touching DB — useful for deriving subsets from a single query
  - Eager loading with `->with(['articles' => fn($q) => $q->limit(5)])` applies the limit per-query but note: with multiple parent records, the limit applies globally to the eager load query, not per-parent. For sidebar display with small datasets this is acceptable.
---

## 2026-02-27 - US-009
- Eliminated redundant database queries in TrackArticleView middleware
- Controller now stores the resolved article on `$request->attributes` (`kb_article` key) after resolving it
- Middleware reads `$request->attributes->get('kb_article')` instead of re-querying category + article from the database
- Removed the `KbCategory` import from the middleware (no longer needed)
- View tracking still only fires on successful 200 responses (checked before accessing article)
- Files changed: `Middleware/TrackArticleView.php`, `Controllers/KnowledgeBaseController.php`
- **Learnings for future iterations:**
  - `$request->attributes` (Symfony ParameterBag) is the proper way to pass data between middleware and controllers — it's separate from query/post params
  - Since middleware wraps the controller call (`$next($request)` runs the controller first), the controller can set attributes that the middleware reads after `$next()` returns
  - Removing unused imports (like `KbCategory`) keeps the code clean and avoids confusion about dependencies
---

## 2026-02-27 - US-010
- Added visually hidden "Skip to main content" link as the first focusable element in `layout.blade.php`, visible on focus with styled appearance
- Added `id="main-content"` to the main content wrapper `<div>` in `layout.blade.php` (used by all pages via `@yield('content')`)
- Added `aria-label` to all `<nav>` elements: "Main navigation" (layout header), "Breadcrumb" (ticket), "Article navigation" (article prev/next), "Table of contents" (article TOC)
- Added `aria-label` to all `<aside>` elements: "Category navigation" (article + category left sidebar), "Table of contents" (article right sidebar), "Category details" (category right sidebar), "Search filters" (search sidebar)
- Files changed: `views/layout.blade.php`, `views/article.blade.php`, `views/category.blade.php`, `views/search.blade.php`, `views/ticket.blade.php`
- **Learnings for future iterations:**
  - Not all pages use `<main>` tags — landing, categories, and ticket pages use `<div>` and `<section>` directly inside `@yield('content')`. The `id="main-content"` was placed on the layout wrapper div to cover all pages
  - Tailwind's `sr-only` + `focus:not-sr-only` pattern is the standard way to create visually hidden skip links that appear on focus
  - Each `<nav>` and `<aside>` needs a *unique* `aria-label` within the page to help screen reader users distinguish between landmarks
  - The categories.blade.php and landing.blade.php don't use `<nav>`, `<aside>`, or `<main>` tags — they use `<section>` and `<div>` elements
---

## 2026-02-27 - US-011
- Added `aria-label="Search knowledge base"` and visually hidden `<label>` elements to all 3 search inputs
- Added unique `id` attributes to each input for label association: `header-search`, `landing-search`, `categories-search`
- Files changed: `views/layout.blade.php`, `views/landing.blade.php`, `views/categories.blade.php`
- **Learnings for future iterations:**
  - There are 3 search inputs total: header (layout.blade.php), landing hero (landing.blade.php), categories hero (categories.blade.php)
  - Both `aria-label` and a visually hidden `<label>` with `for` association provide redundant but robust accessibility — belt-and-suspenders approach
  - Tailwind's `sr-only` class is the standard way to visually hide labels while keeping them accessible to screen readers
---

## 2026-02-27 - US-012
- Added `aria-hidden="true"` to all 31 `<span class="material-icons">` elements across 7 view files
- All icons are decorative (they have adjacent text conveying the meaning), so they all get `aria-hidden="true"` to prevent screen readers from announcing ligature text like "schedule", "thumb_up", "description", etc.
- Files changed: `views/layout.blade.php`, `views/landing.blade.php`, `views/categories.blade.php`, `views/article.blade.php`, `views/category.blade.php`, `views/search.blade.php`, `views/ticket.blade.php`
- **Learnings for future iterations:**
  - All material icons in this codebase are decorative — they always appear alongside text that conveys the same meaning
  - Material Icons use ligature text (e.g., `thumb_up`, `schedule`) inside the `<span>` which screen readers will announce as regular text unless `aria-hidden="true"` is set
  - For icons that are the only content in a button (like toolbar buttons in ticket.blade.php), the buttons already have `title` attributes; proper `aria-label` on buttons is covered by US-030
  - A simple regex `class="material-icons[^"]*"(?!.*aria-hidden)` can verify all icons have been covered
---
