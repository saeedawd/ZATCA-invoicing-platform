<?php

namespace Tests\Feature;

use App\Livewire\Marketing\ContactForm;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MarketingPagesTest extends TestCase
{
    public function test_home_page_has_full_seo_signals(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('name="description"', false);
        $response->assertSee('name="robots"', false);
        $response->assertSee('index, follow', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('og:title', false);
        $response->assertSee('twitter:card', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('FAQPage', false);
        $response->assertSee('فواتير زاتكا', false);
    }

    public function test_all_public_marketing_pages_are_indexable(): void
    {
        $routes = [
            'home',
            'about',
            'contact',
            'marketing.electronic-invoicing',
            'marketing.tax-invoice',
            'marketing.quotations',
            'marketing.free-invoice-software',
            'marketing.zatca-e-invoicing',
            'privacy',
            'terms',
        ];

        foreach ($routes as $name) {
            $response = $this->get(route($name));
            $response->assertOk();
            $response->assertSee('index, follow', false);
            $response->assertSee('rel="canonical"', false);
            $response->assertSee('og:description', false);
        }
    }

    public function test_login_page_is_noindex(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('noindex, nofollow', false);
    }

    public function test_sitemap_lists_only_public_pages(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $response->assertSee(route('home'), false);
        $response->assertSee(route('marketing.electronic-invoicing'), false);
        $response->assertSee(route('privacy'), false);
        $response->assertDontSee('/login', false);
        $response->assertDontSee('/dashboard', false);
        $response->assertSee('<lastmod>', false);
    }

    public function test_robots_txt_protects_private_areas(): void
    {
        $response = $this->get(route('robots'));

        $response->assertOk();
        $response->assertSee('Disallow: /login', false);
        $response->assertSee('Disallow: /register', false);
        $response->assertSee('Disallow: /dashboard', false);
        $response->assertSee('Sitemap:', false);
        $response->assertSee('/sitemap.xml', false);
    }

    public function test_contact_form_sends_mail(): void
    {
        Mail::fake();

        Livewire::test(ContactForm::class)
            ->set('name', 'أحمد')
            ->set('email', 'ahmed@example.com')
            ->set('message', 'أرغب في معرفة المزيد عن المنصة.')
            ->call('send')
            ->assertSet('sent', true)
            ->assertHasNoErrors();

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
            return $mail->hasTo('info@zatca.app')
                && $mail->name === 'أحمد'
                && $mail->email === 'ahmed@example.com'
                && str_contains($mail->body, 'المنصة');
        });
    }

    public function test_contact_form_honeypot_skips_mail(): void
    {
        Mail::fake();

        Livewire::test(ContactForm::class)
            ->set('name', 'سبام')
            ->set('email', 'spam@example.com')
            ->set('message', 'رسالة غير مرغوبة طويلة بما يكفي.')
            ->set('website', 'https://spam.test')
            ->call('send')
            ->assertSet('sent', true);

        Mail::assertNothingSent();
    }

    public function test_contact_page_shows_company_email(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('info@zatca.app', false);
    }
}
