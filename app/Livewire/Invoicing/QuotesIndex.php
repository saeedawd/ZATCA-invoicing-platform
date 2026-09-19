<?php

namespace App\Livewire\Invoicing;

use App\Domains\Invoicing\Models\Quote;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class QuotesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $quotes = Quote::query()
            ->with('party:id,name')
            ->when($this->search !== '', function ($q) {
                $term = trim($this->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('quote_number', 'like', $term.'%')
                        ->orWhere('quote_number', 'like', '%'.$term.'%');
                });
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest('id')
            ->paginate(15);

        return view('livewire.invoicing.quotes-index', compact('quotes'))
            ->title('عروض الأسعار');
    }
}
