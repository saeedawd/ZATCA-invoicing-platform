<?php

namespace Tests\Feature;

use App\Domains\Invoicing\Services\InvoicePdfRenderer;
use App\Domains\Invoicing\Support\InvoiceTemplateCatalog;
use App\Domains\Organization\Models\Organization;
use App\Domains\Tenancy\Support\TenantContext;
use App\Livewire\Organization\InvoiceDesignSettings;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceDesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_can_save_invoice_design_settings(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        TenantContext::set($user->tenant);

        Livewire::actingAs($user)
            ->test(InvoiceDesignSettings::class)
            ->set('invoice_template', 'modern')
            ->set('show_logo', false)
            ->set('brand_primary', '#0f766e')
            ->set('brand_secondary', '#14b8a6')
            ->set('invoice_footer', 'شكراً لتعاملكم معنا')
            ->call('save')
            ->assertHasNoErrors();

        $org = Organization::withoutGlobalScopes()
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $this->assertSame('modern', $org->invoice_template);
        $this->assertFalse($org->show_logo);
        $this->assertSame('#0f766e', $org->brand_primary);
        $this->assertSame('#14b8a6', $org->brand_secondary);
        $this->assertSame('شكراً لتعاملكم معنا', $org->invoice_footer);
    }

    public function test_selecting_template_persists_immediately(): void
    {
        $user = User::factory()->create();
        TenantContext::set($user->tenant);

        Livewire::actingAs($user)
            ->test(InvoiceDesignSettings::class)
            ->call('selectTemplate', 'elegant')
            ->assertSet('invoice_template', 'elegant')
            ->assertSet('brand_primary', '#1c1917')
            ->assertSet('brand_secondary', '#b45309');

        $org = Organization::withoutGlobalScopes()
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $this->assertSame('elegant', $org->invoice_template);
        $this->assertSame('#1c1917', $org->brand_primary);
        $this->assertSame('#b45309', $org->brand_secondary);
    }

    public function test_can_upload_logo_and_render_preview_with_and_without_logo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        TenantContext::set($user->tenant);

        $logo = UploadedFile::fake()->image('logo.png', 200, 80);

        Livewire::actingAs($user)
            ->test(InvoiceDesignSettings::class)
            ->set('invoice_template', 'classic')
            ->set('show_logo', true)
            ->set('logo', $logo)
            ->call('save')
            ->assertHasNoErrors();

        $org = Organization::withoutGlobalScopes()
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $this->assertNotEmpty($org->logo_path);
        Storage::disk('public')->assertExists($org->logo_path);

        $renderer = app(InvoicePdfRenderer::class);

        $withLogo = $renderer->previewBinary($org, [
            'invoice_template' => 'bold',
            'show_logo' => true,
        ]);
        $withoutLogo = $renderer->previewBinary($org, [
            'invoice_template' => 'minimal',
            'show_logo' => false,
        ]);

        $this->assertStringStartsWith('%PDF', $withLogo);
        $this->assertStringStartsWith('%PDF', $withoutLogo);

        foreach (InvoiceTemplateCatalog::keys() as $key) {
            $pdf = $renderer->previewBinary($org, [
                'invoice_template' => $key,
                'show_logo' => true,
                'brand_primary' => '#123456',
                'brand_secondary' => '#abcdef',
            ]);
            $this->assertStringStartsWith('%PDF', $pdf, "Template {$key} should render a PDF");
        }
    }

    public function test_invoice_design_page_is_reachable(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('organization.invoice-design'))
            ->assertOk()
            ->assertSee('تصميم الفاتورة');
    }
}
