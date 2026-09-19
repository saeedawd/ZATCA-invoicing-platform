<?php

namespace App\Domains\Invoicing\Support;

class InvoiceTemplateCatalog
{
    public const DEFAULT = 'classic';

    /**
     * @return array<string, array{key: string, name_ar: string, description: string, preview_primary: string, preview_secondary: string}>
     */
    public static function all(): array
    {
        return [
            'classic' => [
                'key' => 'classic',
                'name_ar' => 'كلاسيك',
                'description' => 'ترويسة ملونة مع خط ذهبي أنيق.',
                'preview_primary' => '#0b5c41',
                'preview_secondary' => '#c9a24b',
            ],
            'minimal' => [
                'key' => 'minimal',
                'name_ar' => 'بسيط',
                'description' => 'تصميم أبيض نظيف بخطوط رفيعة.',
                'preview_primary' => '#1f2937',
                'preview_secondary' => '#9ca3af',
            ],
            'modern' => [
                'key' => 'modern',
                'name_ar' => 'عصري',
                'description' => 'شريط جانبي ملون وهيدر عريض.',
                'preview_primary' => '#0f766e',
                'preview_secondary' => '#14b8a6',
            ],
            'elegant' => [
                'key' => 'elegant',
                'name_ar' => 'أنيق',
                'description' => 'ترويسة فاتحة مع تفاصيل ذهبية.',
                'preview_primary' => '#1c1917',
                'preview_secondary' => '#b45309',
            ],
            'bold' => [
                'key' => 'bold',
                'name_ar' => 'جريء',
                'description' => 'هيدر قوي ورمز QR بارز.',
                'preview_primary' => '#14532d',
                'preview_secondary' => '#f59e0b',
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function isValid(string $key): bool
    {
        return isset(self::all()[$key]);
    }

    public static function resolve(string $key): string
    {
        return self::isValid($key) ? $key : self::DEFAULT;
    }

    public static function viewName(string $key): string
    {
        return 'pdf.templates.'.self::resolve($key);
    }
}
