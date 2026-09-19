<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Support\Seo\SeoManager;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(SeoManager $seo): Response
    {
        $sitemap = $seo->absoluteUrl('/sitemap.xml');

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /dashboard',
            'Disallow: /organization',
            'Disallow: /parties',
            'Disallow: /products',
            'Disallow: /zatca',
            'Disallow: /reports',
            'Disallow: /quotes',
            'Disallow: /invoices',
            'Disallow: /purchases',
            'Disallow: /profile',
            'Disallow: /verify-email',
            'Disallow: /confirm-password',
            'Disallow: /reset-password',
            'Disallow: /forgot-password',
            '',
            'Sitemap: '.$sitemap,
            '',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
