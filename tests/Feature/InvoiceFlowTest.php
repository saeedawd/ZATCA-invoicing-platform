<?php

namespace Tests\Feature;

use App\Domains\Catalog\Models\Party;
use App\Domains\Invoicing\Services\InvoiceService;
use App\Domains\Organization\Models\Organization;
use App\Domains\Tenancy\Support\TenantContext;
use App\Domains\Zatca\Jobs\SubmitInvoiceToZatcaJob;
use App\Domains\Zatca\Models\ZatcaDevice;
use App\Domains\Zatca\Services\ZatcaOnboardingService;
use App\Domains\Zatca\Services\ZatcaSubmitService;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InvoiceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        config(['zatca.simulation_mode' => true]);
    }

    public function test_can_issue_sales_invoice_and_queue_zatca_submission(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        TenantContext::set($user->tenant);

        Organization::query()->where('tenant_id', $user->tenant_id)->update([
            'legal_name_ar' => 'شركة الاختبار',
            'vat_number' => '300000000000003',
            'cr_number' => '1010101010',
            'building_number' => '1234',
            'street' => 'طريق الملك',
            'district' => 'العليا',
            'city' => 'الرياض',
            'postal_code' => '12345',
        ]);

        $organization = Organization::withoutGlobalScopes()
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        app(ZatcaOnboardingService::class)->createDevice(
            $organization,
            '123456',
            'sandbox'
        );

        $this->assertDatabaseHas('zatca_devices', [
            'tenant_id' => $user->tenant_id,
            'status' => 'onboarded',
        ]);

        $party = Party::create([
            'tenant_id' => $user->tenant_id,
            'type' => 'customer',
            'name' => 'عميل نقدي',
        ]);

        $service = app(InvoiceService::class);
        $invoice = $service->createDraft($user, [
            'direction' => 'sales',
            'invoice_type' => 'simplified',
            'document_type_code' => '388',
            'party_id' => $party->id,
            'issue_date' => now()->toDateString(),
        ], [
            [
                'description' => 'خدمة',
                'quantity' => 2,
                'unit_price' => 100,
                'discount' => 0,
                'tax_category' => 'S',
                'tax_rate' => 15,
            ],
        ]);

        $issued = $service->issue($invoice, $user);

        $this->assertSame('issued', $issued->status);
        $this->assertNotEmpty($issued->invoice_number);
        $this->assertNotEmpty($issued->invoice_hash);
        $this->assertNotEmpty($issued->qr_tlv_base64);
        $this->assertSame('230.00', number_format((float) $issued->total_amount, 2, '.', ''));

        Queue::assertPushed(SubmitInvoiceToZatcaJob::class);

        $submission = app(ZatcaSubmitService::class)->submit($issued->fresh());
        $this->assertSame('accepted', $submission->status);
        $this->assertSame('reported', $issued->fresh()->zatca_status);
        $this->assertInstanceOf(ZatcaDevice::class, $issued->fresh()->device);
    }
}
