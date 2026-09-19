<?php

namespace Tests\Feature;

use App\Domains\Catalog\Models\Party;
use App\Domains\Organization\Models\Organization;
use App\Domains\Tenancy\Support\TenantContext;
use App\Livewire\Invoicing\InvoiceForm;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceQuickAddTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_can_quick_add_customer_from_invoice_form(): void
    {
        $user = User::factory()->create();
        TenantContext::set($user->tenant);

        $component = Livewire::actingAs($user)
            ->test(InvoiceForm::class, ['direction' => 'sales'])
            ->call('openQuickParty')
            ->set('quickPartyName', 'عميل سريع')
            ->set('quickPartyCity', 'الرياض')
            ->call('saveQuickParty')
            ->assertHasNoErrors()
            ->assertSet('showQuickParty', false);

        $party = Party::withoutGlobalScopes()
            ->where('tenant_id', $user->tenant_id)
            ->where('name', 'عميل سريع')
            ->first();

        $this->assertNotNull($party);
        $this->assertSame('customer', $party->type);
        $this->assertSame($party->id, $component->get('party_id'));
    }

    public function test_can_quick_add_product_and_fill_line(): void
    {
        $user = User::factory()->create();
        TenantContext::set($user->tenant);

        Livewire::actingAs($user)
            ->test(InvoiceForm::class, ['direction' => 'sales'])
            ->call('openQuickProduct', 0)
            ->set('quickProductName', 'خدمة سريعة')
            ->set('quickProductPrice', '150')
            ->set('quickProductTaxRate', '15')
            ->call('saveQuickProduct')
            ->assertHasNoErrors()
            ->assertSet('showQuickProduct', false)
            ->assertSet('lines.0.description', 'خدمة سريعة')
            ->assertSet('lines.0.unit_price', '150.00');

        $this->assertDatabaseHas('products', [
            'tenant_id' => $user->tenant_id,
            'name_ar' => 'خدمة سريعة',
        ]);
    }

    public function test_invoice_create_page_shows_quick_add_actions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('invoices.create'))
            ->assertOk()
            ->assertSee('إضافة جديد')
            ->assertSee('منتج جديد')
            ->assertSee('معاينة الفاتورة');
    }

    public function test_can_preview_invoice_pdf_before_issue(): void
    {
        $user = User::factory()->create();
        TenantContext::set($user->tenant);

        Organization::withoutGlobalScopes()
            ->where('tenant_id', $user->tenant_id)
            ->update([
                'legal_name_ar' => 'شركة المعاينة',
                'vat_number' => '300000000000003',
            ]);

        Livewire::actingAs($user)
            ->test(InvoiceForm::class, ['direction' => 'sales'])
            ->set('invoice_type', 'simplified')
            ->set('issue_date', now()->toDateString())
            ->set('lines.0.description', 'خدمة معاينة')
            ->set('lines.0.quantity', '2')
            ->set('lines.0.unit_price', '100')
            ->set('lines.0.tax_rate', '15')
            ->call('previewInvoice')
            ->assertHasNoErrors()
            ->assertFileDownloaded();
    }
}
