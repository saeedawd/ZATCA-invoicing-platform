<?php

namespace App\Support\Seo;

use Illuminate\Support\Facades\Route;

class SeoManager
{
    public function canonicalBase(): string
    {
        return rtrim((string) config('seo.canonical_base', config('app.url')), '/');
    }

    public function absoluteUrl(string $path = '/'): string
    {
        $path = '/'.ltrim($path, '/');

        if ($path === '/') {
            return $this->canonicalBase().'/';
        }

        return $this->canonicalBase().rtrim($path, '/');
    }

    public function canonicalForRoute(?string $routeName = null, array $parameters = []): string
    {
        if (! $routeName || ! Route::has($routeName)) {
            return $this->absoluteUrl('/');
        }

        $relative = parse_url(route($routeName, $parameters, false), PHP_URL_PATH) ?: '/';

        return $this->absoluteUrl($relative);
    }

    public function page(string $key): ?array
    {
        $page = config("seo.pages.{$key}");

        return is_array($page) ? $page : null;
    }

    public function indexablePages(): array
    {
        return collect(config('seo.pages', []))
            ->filter(fn ($page) => ($page['indexable'] ?? false) === true)
            ->all();
    }

    public function ogImageUrl(?string $image = null): string
    {
        $path = $image ?: config('seo.og_image', '/images/og-default.png');

        if (str_starts_with((string) $path, 'http://') || str_starts_with((string) $path, 'https://')) {
            return (string) $path;
        }

        return $this->absoluteUrl((string) $path);
    }

    public function logoUrl(): string
    {
        $logo = config('seo.organization.logo', '/logo.png');

        if (str_starts_with((string) $logo, 'http://') || str_starts_with((string) $logo, 'https://')) {
            return (string) $logo;
        }

        return $this->absoluteUrl((string) $logo);
    }

    /**
     * @param  list<array{name: string, url: string}>  $breadcrumbs
     * @param  list<array{question: string, answer: string}>  $faqs
     * @param  list<string>  $schemaFlags
     * @return array<string, mixed>
     */
    public function graph(
        string $pageTitle,
        string $pageDescription,
        string $canonicalUrl,
        array $schemaFlags = ['organization', 'website', 'webpage'],
        array $breadcrumbs = [],
        array $faqs = [],
    ): array {
        $siteName = (string) config('seo.name', 'فواتير زاتكا');
        $orgUrl = $this->canonicalBase();
        $org = config('seo.organization', []);
        $nodes = [];

        if (in_array('organization', $schemaFlags, true)) {
            $nodes[] = [
                '@type' => 'Organization',
                '@id' => $orgUrl.'/#organization',
                'name' => $org['name'] ?? $siteName,
                'alternateName' => $org['alternate_name'] ?? config('seo.english_name'),
                'url' => $orgUrl,
                'email' => $org['email'] ?? config('seo.contact_to'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $this->logoUrl(),
                ],
                'description' => $org['disclaimer'] ?? config('seo.default_description'),
                'areaServed' => [
                    '@type' => 'Country',
                    'name' => 'Saudi Arabia',
                ],
                'contactPoint' => [
                    [
                        '@type' => 'ContactPoint',
                        'contactType' => 'customer support',
                        'email' => $org['email'] ?? config('seo.contact_to'),
                        'availableLanguage' => ['Arabic'],
                        'areaServed' => 'SA',
                    ],
                ],
            ];
        }

        if (in_array('website', $schemaFlags, true)) {
            $nodes[] = [
                '@type' => 'WebSite',
                '@id' => $orgUrl.'/#website',
                'url' => $orgUrl,
                'name' => $siteName,
                'alternateName' => config('seo.english_name'),
                'description' => config('seo.default_description'),
                'inLanguage' => 'ar-SA',
                'publisher' => [
                    '@id' => $orgUrl.'/#organization',
                ],
            ];
        }

        if (in_array('software', $schemaFlags, true)) {
            $nodes[] = [
                '@type' => 'SoftwareApplication',
                'name' => $siteName,
                'alternateName' => config('seo.english_name'),
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'Web',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'SAR',
                ],
                'description' => config('seo.default_description'),
                'url' => $orgUrl,
                'inLanguage' => 'ar-SA',
                'featureList' => [
                    'إصدار فواتير إلكترونية',
                    'إصدار عروض أسعار',
                    'فواتير مبيعات ومشتريات',
                    'مساعدة على الالتزام بمتطلبات الفوترة الإلكترونية في السعودية',
                    'تقارير ضريبية',
                ],
            ];
        }

        if (in_array('webpage', $schemaFlags, true)) {
            $nodes[] = [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl.'#webpage',
                'url' => $canonicalUrl,
                'name' => $pageTitle,
                'description' => $pageDescription,
                'inLanguage' => 'ar-SA',
                'isPartOf' => [
                    '@id' => $orgUrl.'/#website',
                ],
                'about' => [
                    '@id' => $orgUrl.'/#organization',
                ],
            ];
        }

        if (in_array('breadcrumb', $schemaFlags, true) && $breadcrumbs !== []) {
            $nodes[] = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect($breadcrumbs)->values()->map(function (array $crumb, int $index) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $crumb['name'],
                        'item' => $crumb['url'],
                    ];
                })->all(),
            ];
        }

        if (in_array('faq', $schemaFlags, true) && $faqs !== []) {
            $nodes[] = [
                '@type' => 'FAQPage',
                'mainEntity' => collect($faqs)->map(fn (array $faq) => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer'],
                    ],
                ])->all(),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_filter($nodes)),
        ];
    }

    /**
     * @return list<array{name: string, url: string}>
     */
    public function breadcrumbsFor(string $pageKey): array
    {
        $page = $this->page($pageKey);

        if (! $page || ! ($page['breadcrumbs'] ?? false)) {
            return [];
        }

        $crumbs = [
            [
                'name' => 'الرئيسية',
                'url' => $this->canonicalForRoute('home'),
            ],
        ];

        if ($pageKey !== 'home') {
            $crumbs[] = [
                'name' => $this->breadcrumbLabel($pageKey, $page),
                'url' => $this->canonicalForRoute($page['route']),
            ];
        }

        return $crumbs;
    }

    /**
     * @param  array<string, mixed>  $page
     */
    protected function breadcrumbLabel(string $pageKey, array $page): string
    {
        return match ($pageKey) {
            'about' => 'من نحن',
            'contact' => 'تواصل معنا',
            'electronic_invoicing' => 'الفوترة الإلكترونية',
            'tax_invoice' => 'الفاتورة الضريبية',
            'quotations' => 'عروض الأسعار',
            'free_invoice_software' => 'برنامج فواتير مجاني',
            'zatca_e_invoicing' => 'التوافق مع زاتكا',
            'privacy' => 'سياسة الخصوصية',
            'terms' => 'شروط الاستخدام',
            default => (string) ($page['title'] ?? $pageKey),
        };
    }
}
