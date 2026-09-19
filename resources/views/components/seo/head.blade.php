@props([
    'page' => null,
    'title' => null,
    'description' => null,
    'canonical' => null,
    'robots' => null,
    'image' => null,
    'type' => 'website',
    'keywords' => null,
    'jsonLd' => null,
    'faqs' => [],
    'breadcrumbs' => null,
    'schema' => null,
])

@php
    /** @var \App\Support\Seo\SeoManager $seo */
    $seo = app(\App\Support\Seo\SeoManager::class);
    $pageConfig = $page ? $seo->page($page) : null;
    $siteName = config('seo.name', 'فواتير زاتكا');

    $rawTitle = $title ?? ($pageConfig['title'] ?? null);
    $pageTitle = $rawTitle
        ? (str_contains($rawTitle, $siteName) ? $rawTitle : $rawTitle.' | '.$siteName)
        : config('seo.default_title');

    $pageDescription = $description
        ?? ($pageConfig['description'] ?? null)
        ?? config('seo.default_description');

    if ($canonical) {
        $canonicalUrl = str_starts_with($canonical, 'http') ? $canonical : $seo->absoluteUrl($canonical);
    } elseif ($pageConfig && ! empty($pageConfig['route'])) {
        $canonicalUrl = $seo->canonicalForRoute($pageConfig['route']);
    } else {
        $path = parse_url(url()->current(), PHP_URL_PATH) ?: '/';
        $canonicalUrl = $seo->absoluteUrl($path);
    }

    $robotsContent = $robots
        ?? (($pageConfig['indexable'] ?? true) ? 'index, follow' : 'noindex, nofollow');

    $ogImage = $seo->ogImageUrl($image);
    $keywordList = $keywords ?? config('seo.keywords', []);
    $keywordsString = is_array($keywordList) ? implode('، ', $keywordList) : (string) $keywordList;
    $geo = config('seo.geo', []);

    $schemaFlags = $schema ?? ($pageConfig['schema'] ?? ['organization', 'website', 'webpage']);
    $crumbItems = $breadcrumbs ?? ($page ? $seo->breadcrumbsFor($page) : []);

    $schemaData = $jsonLd ?? $seo->graph(
        $pageTitle,
        $pageDescription,
        $canonicalUrl,
        $schemaFlags,
        $crumbItems,
        $faqs,
    );
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
@if ($keywordsString !== '' && ! str_contains($robotsContent, 'noindex'))
    <meta name="keywords" content="{{ $keywordsString }}">
@endif
<meta name="robots" content="{{ $robotsContent }}">
<meta name="googlebot" content="{{ $robotsContent }}">
<meta name="author" content="{{ $siteName }}">
<meta name="language" content="Arabic">
<meta name="geo.region" content="{{ $geo['region'] ?? 'SA' }}">
<meta name="geo.placename" content="{{ $geo['placename'] ?? 'Saudi Arabia' }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
<link rel="alternate" hreflang="ar-SA" href="{{ $canonicalUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:locale" content="ar_SA">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="{{ config('seo.og_image_width', 1200) }}">
<meta property="og:image:height" content="{{ config('seo.og_image_height', 630) }}">
<meta property="og:image:alt" content="{{ $siteName }} — فواتير إلكترونية وعروض أسعار">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">

@if (! empty($schemaData['@graph']))
    <script type="application/ld+json">{!! json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endif
