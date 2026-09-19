<?php

namespace App\Livewire\Invoicing;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoicePayment;
use App\Domains\Invoicing\Services\PaymentService;
use App\Domains\Zatca\Jobs\SubmitInvoiceToZatcaJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

#[Layout('layouts.app')]
class InvoiceShow extends Component
{
    public Invoice $invoice;

    public string $payment_amount = '';

    public string $payment_method = 'cash';

    public string $payment_paid_at = '';

    public string $payment_reference = '';

    public string $payment_notes = '';

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load([
            'lines',
            'party:id,name,vat_number,type,email',
            'taxTotals',
            'submissions',
            'payments.creator:id,name',
            'references.referencedInvoice:id,invoice_number',
        ]);

        $this->payment_paid_at = now()->toDateString();
        $this->payment_amount = $invoice->status === 'issued'
            ? number_format((float) $invoice->balance_due, 2, '.', '')
            : '';
    }

    public function recordPayment(PaymentService $payments): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $validated = $this->validate([
            'payment_amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', Rule::in(InvoicePayment::METHODS)],
            'payment_paid_at' => ['required', 'date'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'payment_notes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'payment_amount' => 'المبلغ',
            'payment_method' => 'طريقة الدفع',
            'payment_paid_at' => 'تاريخ الدفع',
            'payment_reference' => 'المرجع',
            'payment_notes' => 'الملاحظات',
        ]);

        try {
            $payments->record($this->invoice, auth()->user(), [
                'amount' => $validated['payment_amount'],
                'method' => $validated['payment_method'],
                'paid_at' => $validated['payment_paid_at'],
                'reference' => $validated['payment_reference'] ?: null,
                'notes' => $validated['payment_notes'] ?: null,
            ]);
        } catch (Throwable $e) {
            $this->addError('payment_amount', $e->getMessage());

            return;
        }

        $this->refreshInvoice();
        $this->payment_method = 'cash';
        $this->payment_reference = '';
        $this->payment_notes = '';
        $this->payment_paid_at = now()->toDateString();
        $this->payment_amount = number_format((float) $this->invoice->balance_due, 2, '.', '');

        session()->flash('status', 'تم تسجيل الدفعة بنجاح.');
    }

    public function deletePayment(int $paymentId, PaymentService $payments): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $payment = $this->invoice->payments()->whereKey($paymentId)->firstOrFail();
        $payments->delete($payment);

        $this->refreshInvoice();
        $this->payment_amount = number_format((float) $this->invoice->balance_due, 2, '.', '');

        session()->flash('status', 'تم حذف الدفعة.');
    }

    public function retryZatca(): void
    {
        abort_unless(auth()->user()->canManage(), 403);
        abort_unless($this->invoice->isSales() && $this->invoice->status === 'issued', 400);

        if (in_array($this->invoice->zatca_status, ['cleared', 'reported'], true)) {
            session()->flash('status', 'الفاتورة مقبولة مسبقاً لدى هيئة الزكاة والضريبة والجمارك.');

            return;
        }

        SubmitInvoiceToZatcaJob::dispatch($this->invoice->id)->onQueue('zatca');
        session()->flash('status', 'تمت إعادة إرسال الفاتورة لطابور الزكاة والضريبة.');
    }

    public function downloadPdf(): StreamedResponse
    {
        abort_unless($this->invoice->pdf_path && Storage::disk('local')->exists($this->invoice->pdf_path), 404);

        return Storage::disk('local')->download($this->invoice->pdf_path, ($this->invoice->invoice_number ?: 'invoice').'.pdf');
    }

    public function emailDocument(\App\Domains\Invoicing\Services\DocumentMailService $mailer): void
    {
        abort_unless(auth()->user()->canManage(), 403);
        abort_unless($this->invoice->status === 'issued', 400);

        try {
            $mailer->sendInvoice($this->invoice);
            session()->flash('status', 'تم إرسال الفاتورة إلى '.$this->invoice->party?->email);
        } catch (\Throwable $e) {
            session()->flash('status', $e->getMessage());
        }
    }

    protected function refreshInvoice(): void
    {
        $this->invoice = $this->invoice->fresh([
            'lines',
            'party:id,name,vat_number,type,email',
            'taxTotals',
            'submissions',
            'payments.creator:id,name',
            'references.referencedInvoice:id,invoice_number',
        ]);
    }

    public function render()
    {
        return view('livewire.invoicing.invoice-show')
            ->title('عرض الفاتورة');
    }
}
