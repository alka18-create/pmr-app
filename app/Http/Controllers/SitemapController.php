<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use App\Models\Member;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => url('/profil'), 'priority' => '0.8'],
            ['loc' => url('/kegiatan'), 'priority' => '0.8'],
            ['loc' => url('/berita'), 'priority' => '0.8'],
            ['loc' => url('/galeri'), 'priority' => '0.7'],
            ['loc' => url('/prestasi'), 'priority' => '0.7'],
            ['loc' => url('/anggota'), 'priority' => '0.7'],
            ['loc' => url('/kontak'), 'priority' => '0.6'],
        ];

        Article::query()
            ->where('is_public', true)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->limit(1000)
            ->get(['slug', 'published_at', 'updated_at'])
            ->each(function ($article) use (&$urls) {
                $urls[] = [
                    'loc' => url('/berita/'.$article->slug),
                    'lastmod' => ($article->updated_at ?? $article->published_at)?->toAtomString(),
                    'priority' => '0.6',
                ];
            });

        Event::query()
            ->where('is_public', true)
            ->whereIn('status', ['published', 'selesai'])
            ->orderByDesc('starts_at')
            ->limit(500)
            ->get(['slug', 'updated_at'])
            ->each(function ($event) use (&$urls) {
                $urls[] = [
                    'loc' => url('/kegiatan/'.$event->slug),
                    'lastmod' => $event->updated_at?->toAtomString(),
                    'priority' => '0.6',
                ];
            });

        Member::query()
            ->where('status', 'aktif')
            ->whereNotNull('slug')
            ->limit(1000)
            ->pluck('slug')
            ->each(function ($slug) use (&$urls) {
                $urls[] = [
                    'loc' => url('/anggota/'.$slug),
                    'priority' => '0.5',
                ];
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        foreach ($urls as $item) {
            $xml .= '  <url>'.PHP_EOL;
            $xml .= '    <loc>'.e($item['loc']).'</loc>'.PHP_EOL;
            if (! empty($item['lastmod'])) {
                $xml .= '    <lastmod>'.e($item['lastmod']).'</lastmod>'.PHP_EOL;
            }
            $xml .= '    <priority>'.e($item['priority'] ?? '0.5').'</priority>'.PHP_EOL;
            $xml .= '  </url>'.PHP_EOL;
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
