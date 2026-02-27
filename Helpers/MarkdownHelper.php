<?php

namespace Packages\EzKnowledgeBase\Helpers;

use Illuminate\Support\Str;

class MarkdownHelper
{
    /**
     * Add slug-based IDs to heading tags (h1-h6) that don't already have one.
     */
    public static function addHeadingIds(string $html): string
    {
        return preg_replace_callback('/<(h[1-6])([^>]*)>(.+?)<\/\1>/i', function ($matches) {
            $tag = $matches[1];
            $attrs = $matches[2];
            $text = strip_tags($matches[3]);
            $id = Str::slug($text);

            // Don't overwrite existing id
            if (preg_match('/\bid\s*=/', $attrs)) {
                return $matches[0];
            }

            return "<{$tag}{$attrs} id=\"{$id}\">{$matches[3]}</{$tag}>";
        }, $html);
    }

    /**
     * Extract h2 headings from HTML for table of contents generation.
     *
     * @return array<int, array{text: string, id: string}>
     */
    public static function extractHeadings(string $html): array
    {
        $headings = [];
        preg_match_all('/<h2[^>]*>(.+?)<\/h2>/i', $html, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $heading) {
                $text = strip_tags($heading);
                $headings[] = [
                    'text' => $text,
                    'id' => Str::slug($text),
                ];
            }
        }

        return $headings;
    }
}
