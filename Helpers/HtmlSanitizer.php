<?php

namespace Packages\EzKnowledgeBase\Helpers;

class HtmlSanitizer
{
    /**
     * Allowlist of HTML tags that markdown rendering can produce.
     * Any tag not in this list will be stripped.
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins', 'mark',
        'a', 'img',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'div', 'span',
        'sup', 'sub', 'abbr',
        'dl', 'dt', 'dd',
        'figure', 'figcaption',
        'details', 'summary',
    ];

    /**
     * Allowlist of attributes per tag. '*' applies to all tags.
     */
    private const ALLOWED_ATTRIBUTES = [
        '*' => ['id', 'class'],
        'a' => ['href', 'title', 'rel', 'target'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'td' => ['colspan', 'rowspan', 'align'],
        'th' => ['colspan', 'rowspan', 'align', 'scope'],
        'ol' => ['start', 'type'],
        'code' => ['class'],
    ];

    /**
     * Sanitize HTML by stripping disallowed tags and attributes.
     * Removes all event handler attributes and javascript: URLs.
     */
    public static function sanitize(string $html): string
    {
        // First, strip any tags not in our allowlist
        $allowedTagString = '<' . implode('><', self::ALLOWED_TAGS) . '>';
        $html = strip_tags($html, $allowedTagString);

        // Remove event handler attributes (onclick, onerror, onload, etc.)
        $html = preg_replace('/\s+on\w+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        // Remove javascript:, vbscript:, data: URLs from href and src attributes
        $html = preg_replace_callback(
            '/(<\w+[^>]*\s)((?:href|src)\s*=\s*)((?:"[^"]*"|\'[^\']*\'))/i',
            function ($matches) {
                $url = trim($matches[3], '"\'');
                $urlScheme = strtolower(trim($url));
                if (preg_match('/^(javascript|vbscript|data):/i', $urlScheme)) {
                    return $matches[1]; // Strip the dangerous attribute entirely
                }
                return $matches[0];
            },
            $html
        );

        return $html;
    }
}
