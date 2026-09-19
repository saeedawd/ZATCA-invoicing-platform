<?php

namespace App\Livewire\Reports;

use App\Domains\Invoicing\Models\Invoice;
use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TaxReport extends Component
{
    public string $from;

    public string $to;

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
    }

    public function render()
    {
        $totals = Invoice::query()
            ->where('status', 'issued')
            ->whereBetween('issue_date', [$this->from, $this->to])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN direction = 'sales' THEN taxable_amount ELSE 0 END), 0) as sales_taxable,
                COALESCE(SUM(CASE WHEN direction = 'sales' THEN tax_amount ELSE 0 END), 0) as sales_tax,
                COALESCE(SUM(CASE WHEN direction = 'sales' THEN total_amount ELSE 0 END), 0) as sales_total,
                COALESCE(SUM(CASE WHEN direction = 'purchase' THEN taxable_amount ELSE 0 END), 0) as purchase_taxable,
                COALESCE(SUM(CASE WHEN direction = 'purchase' THEN tax_amount ELSE 0 END), 0) as purchase_tax,
                COALESCE(SUM(CASE WHEN direction = 'purchase' THEN total_amount ELSE 0 END), 0) as purchase_total
            ")
            ->first();

        return view('livewire.reports.tax-report', [
            'salesTaxable' => (float) ($totals->sales_taxable ?? 0),
            'salesTax' => (float) ($totals->sales_tax ?? 0),
            'salesTotal' => (float) ($totals->sales_total ?? 0),
            'purchaseTaxable' => (float) ($totals->purchase_taxable ?? 0),
            'purchaseTax' => (float) ($totals->purchase_tax ?? 0),
            'purchaseTotal' => (float) ($totals->purchase_total ?? 0),
            'byZatcaStatus' => Invoice::query()
                ->where('direction', 'sales')
                ->where('status', 'issued')
                ->whereBetween('issue_date', [$this->from, $this->to])
                ->selectRaw('zatca_status, count(*) as total')
                ->groupBy('zatca_status')
                ->pluck('total', 'zatca_status'),
            'auditLogs' => AuditLog::query()
                ->latest('id')
                ->limit(20)
                ->get(['id', 'action', 'created_at']),
        ])->title('التقارير');
    }
}
