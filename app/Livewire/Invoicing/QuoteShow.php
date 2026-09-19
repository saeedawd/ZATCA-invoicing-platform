<?php

namespace App\Livewire\Invoicing;

use App\Domains\Invoicing\Models\Quote;
use App\Domains\Invoicing\Services\QuotePdfRenderer;
use App\Domains\Invoicing\Services\QuoteService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.app')]
class QuoteShow extends Component
{
    public Quote $quote;

    public function mount(Quote $quote): void
    {
        $this->quote = $quote->load([
            'lines',
            'party:id,name,vat_number,type,email',
            'convertedInvoice:id,invoice_number,status',
        ]);
    }

    public function send(QuoteService $service)
    {
        abort_unless(auth()->user()->canManage(), 403);

        try {
            $service->send($this->quote, auth()->user());
        } catch (Throwable $e) {
            session()->flash('status', $e->getMessage());

            return;
        }

        $this->refreshQuote();
        session()->flash('status', 'تم إرسال عرض السعر.');
    }

    public function accept(QuoteService $service): void
    {
        abort_unless(auth()->user()->canManage(), 403);
        $service->accept($this->quote, auth()->user());
        $this->refreshQuote();
        session()->flash('status', 'تم قبول عرض السعر.');
    }

    public function reject(QuoteService $service): void
    {
        abort_unless(auth()->user()->canManage(), 403);
        $service->reject($this->quote, auth()->user());
        $this->refreshQuote();
        session()->flash('status', 'تم رفض عرض السعر.');
    }

    public function convertToInvoice(QuoteService $service)
    {
        abort_unless(auth()->user()->canManage(), 403);

        try {
            $invoice = $service->convertToInvoice($this->quote, auth()->user());
        } catch (Throwable $e) {
            session()->flash('status', $e->getMessage());

            return null;
        }

        session()->flash('status', 'تم تحويل عرض السعر إلى مسودة فاتورة.');

        return $this->redirect(route('invoices.edit', $invoice), navigate: true);
    }

    public function downloadPdf(QuotePdfRenderer $renderer)
    {
        abort_unless(in_array($this->quote->status, ['sent', 'accepted', 'rejected', 'converted'], true), 404);

        $path = $renderer->render($this->quote);

        return Storage::disk('local')->download($path, ($this->quote->quote_number ?: 'quote').'.pdf');
    }

    public function emailDocument(\App\Domains\Invoicing\Services\DocumentMailService $mailer): void
    {
        abort_unless(auth()->user()->canManage(), 403);
        abort_unless(in_array($this->quote->status, ['sent', 'accepted', 'rejected', 'converted'], true), 400);

        try {
            $mailer->sendQuote($this->quote);
            session()->flash('status', 'تم إرسال عرض السعر إلى '.$this->quote->party?->email);
            $this->refreshQuote();
        } catch (Throwable $e) {
            session()->flash('status', $e->getMessage());
        }
    }

    protected function refreshQuote(): void
    {
        $this->quote = $this->quote->fresh([
            'lines',
            'party:id,name,vat_number,type,email',
            'convertedInvoice:id,invoice_number,status',
        ]);
    }

    public function render()
    {
        return view('livewire.invoicing.quote-show')
            ->title('عرض السعر');
    }
}
