## Codebase Patterns
- This is a standalone Laravel package at `packages/EzKnowledgeBase/` with no own composer.json — PSR-4 autoloaded via host app
- Controllers are in `Controllers/`, views in `views/`, config in `config/kb.php`, middleware in `Middleware/`
- Markdown rendering uses `Str::markdown()` with CommonMark options (`html_input`, `allow_unsafe_links`)
- Views use `{!! $parsedBody !!}` for rendered HTML — always sanitize before passing to view
- No dedicated test suite — use `php -l` for syntax checking at minimum
- Cache keys follow pattern: `kb_article_{slug}`, `kb_categories_with_counts`, `kb_featured_articles`, `kb_all_categories_with_top_articles`
- Namespace: `Packages\EzKnowledgeBase\`

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
