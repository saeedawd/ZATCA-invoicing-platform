<?php

namespace App\Domains\Zatca\Jobs;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Tenancy\Support\TenantContext;
use App\Domains\Zatca\Services\ZatcaSubmitService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubmitInvoiceToZatcaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $invoiceId) {}

    public function handle(ZatcaSubmitService $submitService): void
    {
        $invoice = Invoice::withoutGlobalScopes()->find($this->invoiceId);

        if (! $invoice || ! $invoice->isSales()) {
            return;
        }

        if (in_array($invoice->zatca_status, ['cleared', 'reported'], true)) {
            return;
        }

        TenantContext::set($invoice->tenant);
        try {
            $submitService->submit($invoice);
        } finally {
            TenantContext::clear();
        }
    }

    public function uniqueId(): string
    {
        return 'submit-invoice-'.$this->invoiceId;
    }
}
