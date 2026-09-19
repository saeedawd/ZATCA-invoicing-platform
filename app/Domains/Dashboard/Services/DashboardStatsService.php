<?php

namespace App\Domains\Dashboard\Services;

use App\Domains\Catalog\Models\Party;
use App\Domains\Catalog\Models\Product;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Zatca\Models\ZatcaDevice;
use App\Domains\Zatca\Models\ZatcaSubmission;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    public function forTenant(int $tenantId): array
    {
        return Cache::remember(
            $this->cacheKey($tenantId),
            now()->addSeconds(45),
            fn () => $this->compute()
        );
    }

    public function forget(int $tenantId): void
    {
        Cache::forget($this->cacheKey($tenantId));
    }

    protected function cacheKey(int $tenantId): string
    {
        return "tenant:{$tenantId}:dashboard-stats";
    }

    /**
     * @return array<string, mixed>
     */
    protected function compute(): array
    {
        $invoiceStats = Invoice::query()
            ->selectRaw("
                COUNT(CASE WHEN direction = 'sales' AND status = 'issued' THEN 1 END) as sales_count,
                COALESCE(SUM(CASE WHEN direction = 'sales' AND status = 'issued' THEN total_amount ELSE 0 END), 0) as sales_total,
                COALESCE(SUM(CASE WHEN direction = 'sales' AND status = 'issued' THEN tax_amount ELSE 0 END), 0) as sales_tax,
                COUNT(CASE WHEN direction = 'purchase' AND status = 'issued' THEN 1 END) as purchase_count,
                COALESCE(SUM(CASE WHEN direction = 'purchase' AND status = 'issued' THEN total_amount ELSE 0 END), 0) as purchase_total,
                COUNT(CASE WHEN zatca_status IN ('cleared', 'reported') THEN 1 END) as zatca_accepted,
                COUNT(CASE WHEN zatca_status = 'rejected' THEN 1 END) as zatca_rejected
            ")
            ->first();

        return [
            'salesCount' => (int) ($invoiceStats->sales_count ?? 0),
            'salesTotal' => (float) ($invoiceStats->sales_total ?? 0),
            'salesTax' => (float) ($invoiceStats->sales_tax ?? 0),
            'purchaseCount' => (int) ($invoiceStats->purchase_count ?? 0),
            'purchaseTotal' => (float) ($invoiceStats->purchase_total ?? 0),
            'zatcaAccepted' => (int) ($invoiceStats->zatca_accepted ?? 0),
            'zatcaRejected' => (int) ($invoiceStats->zatca_rejected ?? 0),
            'partiesCount' => Party::query()->count(),
            'productsCount' => Product::query()->count(),
            'recentInvoices' => Invoice::query()
                ->with(['party:id,name'])
                ->latest('id')
                ->limit(8)
                ->get(['id', 'invoice_number', 'direction', 'party_id', 'total_amount', 'status', 'created_at']),
            'recentSubmissions' => ZatcaSubmission::query()
                ->with(['invoice:id,invoice_number'])
                ->latest('id')
                ->limit(8)
                ->get(['id', 'invoice_id', 'type', 'status', 'created_at']),
            'device' => ZatcaDevice::query()
                ->where('status', 'onboarded')
                ->latest('id')
                ->first(['id', 'device_serial', 'status', 'environment']),
        ];
    }
}
