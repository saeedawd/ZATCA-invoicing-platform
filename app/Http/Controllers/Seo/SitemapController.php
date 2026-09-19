<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Support\Seo\SeoManager;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SeoManager $seo): Response
    {
        $urls = collect($seo->indexablePages())
            ->map(function (array $page) use ($seo) {
                $view = $page['view'] ?? null;
                $lastmod = now()->toDateString();

                if (is_string($view)) {
                    $path = resource_path('views/'.str_replace('.', '/', $view).'.blade.php');
                    if (is_file($path)) {
                        $lastmod = date('Y-m-d', filemtime($path));
                    }
                }

                return [
                    'loc' => $seo->canonicalForRoute($page['route']),
                    'lastmod' => $lastmod,
                    'changefreq' => $page['changefreq'] ?? 'monthly',
                    'priority' => $page['priority'] ?? '0.5',
                ];
            })
            ->values();

        return response()
            ->view('marketing.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
