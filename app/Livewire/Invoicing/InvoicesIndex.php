<?php

namespace App\Livewire\Invoicing;

use App\Domains\Invoicing\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class InvoicesIndex extends Component
{
    use WithPagination;

    public string $direction = 'sales';

    public string $search = '';

    public string $status = '';

    public string $paymentStatus = '';

    public function mount(?string $direction = null): void
    {
        $this->direction = $direction
            ?? (request()->routeIs('purchases.*') ? 'purchase' : 'sales');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $invoices = Invoice::query()
            ->with('party:id,name')
            ->where('direction', $this->direction)
            ->when($this->search !== '', function ($q) {
                $term = trim($this->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('invoice_number', 'like', $term.'%')
                        ->orWhere('invoice_number', 'like', '%'.$term.'%');
                });
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->paymentStatus !== '', fn ($q) => $q->where('payment_status', $this->paymentStatus))
            ->latest('id')
            ->paginate(15);

        return view('livewire.invoicing.invoices-index', compact('invoices'))
            ->title($this->direction === 'purchase' ? 'فواتير المشتريات' : 'فواتير المبيعات');
    }
}
