<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_404_page_renders_branded_arabic_content(): void
    {
        $response = $this->get('/this-page-does-not-exist-zatca-seo');

        $response->assertNotFound();
        $response->assertSee('الصفحة اللي تدور عليها غير موجودة', false);
        $response->assertSee('noindex, nofollow', false);
        $response->assertSee('فواتير زاتكا', false);
        $response->assertSee(url('/'), false);
    }

    public function test_403_view_is_available(): void
    {
        $html = view('errors.403')->render();

        $this->assertStringContainsString('403', $html);
        $this->assertStringContainsString('ما عندك صلاحية', $html);
        $this->assertStringContainsString('noindex, nofollow', $html);
    }

    public function test_500_and_503_views_are_available(): void
    {
        $this->assertStringContainsString('خطأ غير متوقع', view('errors.500')->render());
        $this->assertStringContainsString('تحت الصيانة', view('errors.503')->render());
        $this->assertStringContainsString('انتهت صلاحية الجلسة', view('errors.419')->render());
        $this->assertStringContainsString('كثرّت المحاولات', view('errors.429')->render());
    }
}
