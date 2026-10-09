<?php

namespace App\Services;

/**
 * Sanitizer HTML ringan tanpa dependensi eksternal.
 * Hanya tag aman untuk konten artikel.
 */
class ContentSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><a><code><pre><img><figure><figcaption><hr><span>';

    public static function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $clean = strip_tags($html, self::ALLOWED_TAGS);
        $clean = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\s(href|src)\s*=\s*("(?:javascript|data):[^"]*"|\'(?:javascript|data):[^\']*\'|(?:javascript|data):[^\s>]+)/i', '', $clean) ?? $clean;

        return trim($clean);
    }
}
