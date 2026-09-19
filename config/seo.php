<?php

return [

    'name' => env('APP_NAME', 'فواتير زاتكا'),

    'english_name' => 'ZATCA Invoices',

    'canonical_base' => rtrim(env('SEO_CANONICAL_BASE', env('APP_URL', 'https://zatca.app')), '/'),

    'default_title' => 'فواتير زاتكا | فواتير إلكترونية مجانية في السعودية',

    'default_description' => 'فواتير زاتكا منصة سعودية مجانية لإصدار الفواتير الإلكترونية وعروض الأسعار المتوافقة مع متطلبات هيئة الزكاة والضريبة والجمارك — بدون رسوم وبدون اشتراك.',

    'contact_to' => env('CONTACT_MAIL_TO', 'info@zatca.app'),

    'og_image' => '/images/og-default.png',

    'og_image_width' => 1200,

    'og_image_height' => 630,

    'keywords' => [
        'الفوترة الإلكترونية',
        'فاتورة إلكترونية',
        'فاتورة ضريبية',
        'إصدار فاتورة',
        'عرض سعر',
        'برنامج فواتير مجاني',
        'فواتير زاتكا',
        'نظام فواتير السعودية',
        'فواتير مبيعات',
        'فواتير مشتريات',
        'ZATCA invoice',
        'electronic invoicing Saudi Arabia',
    ],

    'geo' => [
        'region' => 'SA',
        'placename' => 'Saudi Arabia',
    ],

    'organization' => [
        'name' => env('APP_NAME', 'فواتير زاتكا'),
        'alternate_name' => 'ZATCA Invoices',
        'url' => rtrim(env('SEO_CANONICAL_BASE', env('APP_URL', 'https://zatca.app')), '/'),
        'email' => env('CONTACT_MAIL_TO', 'info@zatca.app'),
        'logo' => '/logo.png',
        'same_as' => [],
        'disclaimer' => 'منصة برمجية خاصة لمساعدة المنشآت على الفوترة الإلكترونية. ليست تابعة لهيئة الزكاة والضريبة والجمارك وليست موقعًا حكوميًا.',
    ],

    /*
    |--------------------------------------------------------------------------
    | سجل الصفحات العامة القابلة للفهرسة
    |--------------------------------------------------------------------------
    */
    'pages' => [

        'home' => [
            'route' => 'home',
            'view' => 'welcome',
            'indexable' => true,
            'title' => 'فواتير زاتكا | فواتير إلكترونية مجانية في السعودية',
            'description' => 'أصدر فاتورة إلكترونية أو عرض سعر مجانًا مع فواتير زاتكا — منصة فوترة سعودية متوافقة مع متطلبات هيئة الزكاة والضريبة والجمارك.',
            'changefreq' => 'weekly',
            'priority' => '1.0',
            'schema' => ['organization', 'website', 'software', 'webpage', 'faq'],
            'breadcrumbs' => false,
        ],

        'about' => [
            'route' => 'about',
            'view' => 'marketing.about',
            'indexable' => true,
            'title' => 'عن فواتير زاتكا | منصة الفوترة الإلكترونية السعودية',
            'description' => 'تعرّف على فواتير زاتكا: منصة سعودية مجانية لإصدار الفواتير الإلكترونية وعروض الأسعار ومساعدة المنشآت على الالتزام بمتطلبات الفوترة الإلكترونية.',
            'changefreq' => 'monthly',
            'priority' => '0.8',
            'schema' => ['organization', 'website', 'webpage'],
            'breadcrumbs' => true,
        ],

        'contact' => [
            'route' => 'contact',
            'view' => 'marketing.contact',
            'indexable' => true,
            'title' => 'تواصل معنا | فواتير زاتكا',
            'description' => 'تواصل مع فريق فواتير زاتكا عبر info@zatca.app لدعم إصدار الفواتير الإلكترونية وعروض الأسعار والربط مع متطلبات الفوترة في السعودية.',
            'changefreq' => 'monthly',
            'priority' => '0.7',
            'schema' => ['organization', 'website', 'webpage'],
            'breadcrumbs' => true,
        ],

        'electronic_invoicing' => [
            'route' => 'marketing.electronic-invoicing',
            'view' => 'marketing.pages.electronic-invoicing',
            'indexable' => true,
            'title' => 'الفوترة الإلكترونية في السعودية | فواتير زاتكا',
            'description' => 'شرح عملي للفوترة الإلكترونية في المملكة العربية السعودية وكيف تصدر فاتورتك الإلكترونية عبر منصة فواتير زاتكا المجانية.',
            'changefreq' => 'monthly',
            'priority' => '0.9',
            'schema' => ['organization', 'website', 'webpage', 'faq', 'breadcrumb'],
            'breadcrumbs' => true,
        ],

        'tax_invoice' => [
            'route' => 'marketing.tax-invoice',
            'view' => 'marketing.pages.tax-invoice',
            'indexable' => true,
            'title' => 'إنشاء فاتورة ضريبية إلكترونية | فواتير زاتكا',
            'description' => 'أنشئ فاتورة ضريبية إلكترونية في السعودية مجانًا عبر فواتير زاتكا، مع إدارة المبيعات والضريبة بما يوافق متطلبات الفوترة الإلكترونية.',
            'changefreq' => 'monthly',
            'priority' => '0.9',
            'schema' => ['organization', 'website', 'webpage', 'faq', 'breadcrumb'],
            'breadcrumbs' => true,
        ],

        'quotations' => [
            'route' => 'marketing.quotations',
            'view' => 'marketing.pages.quotations',
            'indexable' => true,
            'title' => 'إنشاء عرض سعر إلكتروني مجانًا | فواتير زاتكا',
            'description' => 'أصدر عرض سعر إلكتروني لعملائك في السعودية ثم حوّله لفاتورة بسهولة عبر فواتير زاتكا — مجانًا وبدون اشتراك.',
            'changefreq' => 'monthly',
            'priority' => '0.85',
            'schema' => ['organization', 'website', 'webpage', 'faq', 'breadcrumb'],
            'breadcrumbs' => true,
        ],

        'free_invoice_software' => [
            'route' => 'marketing.free-invoice-software',
            'view' => 'marketing.pages.free-invoice-software',
            'indexable' => true,
            'title' => 'برنامج فواتير مجاني في السعودية | فواتير زاتكا',
            'description' => 'برنامج فواتير إلكترونية مجاني 100٪ للمنشآت السعودية: إصدار فواتير، عروض أسعار، مبيعات ومشتريات بدون رسوم.',
            'changefreq' => 'monthly',
            'priority' => '0.9',
            'schema' => ['organization', 'website', 'software', 'webpage', 'faq', 'breadcrumb'],
            'breadcrumbs' => true,
        ],

        'zatca_e_invoicing' => [
            'route' => 'marketing.zatca-e-invoicing',
            'view' => 'marketing.pages.zatca-e-invoicing',
            'indexable' => true,
            'title' => 'فوترة متوافقة مع متطلبات زاتكا | فواتير زاتكا',
            'description' => 'تعرّف كيف تساعدك منصة فواتير زاتكا على إصدار فواتير إلكترونية بما يوافق متطلبات هيئة الزكاة والضريبة والجمارك — كمنصة خاصة مستقلة.',
            'changefreq' => 'monthly',
            'priority' => '0.9',
            'schema' => ['organization', 'website', 'webpage', 'faq', 'breadcrumb'],
            'breadcrumbs' => true,
        ],

        'privacy' => [
            'route' => 'privacy',
            'view' => 'marketing.pages.privacy',
            'indexable' => true,
            'title' => 'سياسة الخصوصية | فواتير زاتكا',
            'description' => 'سياسة خصوصية فواتير زاتكا: كيف نتعامل مع بيانات المنشآت والمستخدمين في منصة الفوترة الإلكترونية السعودية.',
            'changefreq' => 'yearly',
            'priority' => '0.3',
            'schema' => ['organization', 'website', 'webpage', 'breadcrumb'],
            'breadcrumbs' => true,
        ],

        'terms' => [
            'route' => 'terms',
            'view' => 'marketing.pages.terms',
            'indexable' => true,
            'title' => 'شروط الاستخدام | فواتير زاتكا',
            'description' => 'شروط استخدام منصة فواتير زاتكا لإصدار الفواتير الإلكترونية وعروض الأسعار في المملكة العربية السعودية.',
            'changefreq' => 'yearly',
            'priority' => '0.3',
            'schema' => ['organization', 'website', 'webpage', 'breadcrumb'],
            'breadcrumbs' => true,
        ],

    ],

];
