<?php

namespace App\Livewire\Catalog;

use App\Domains\Catalog\Models\Party;
use App\Domains\Dashboard\Services\DashboardStatsService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PartiesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $type = 'customer';

    public string $name = '';

    public string $vat_number = '';

    public string $cr_number = '';

    public string $building_number = '';

    public string $street = '';

    public string $district = '';

    public string $city = '';

    public string $postal_code = '';

    public string $email = '';

    public string $phone = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $party = Party::findOrFail($id);
        $this->editingId = $party->id;
        $this->fill($party->only([
            'type', 'name', 'vat_number', 'cr_number', 'building_number',
            'street', 'district', 'city', 'postal_code', 'email', 'phone',
        ]));
        $this->showForm = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $validated = $this->validate([
            'type' => ['required', 'in:customer,supplier,both'],
            'name' => ['required', 'string', 'max:255'],
            'vat_number' => ['nullable', 'regex:/^3\d{13}3$/'],
            'cr_number' => ['nullable', 'string', 'max:20'],
            'building_number' => ['nullable', 'string', 'max:10'],
            'street' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'digits:5'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        if ($this->editingId) {
            Party::findOrFail($this->editingId)->update($validated);
        } else {
            Party::create($validated + ['tenant_id' => auth()->user()->tenant_id]);
        }

        app(DashboardStatsService::class)->forget((int) auth()->user()->tenant_id);
        $this->resetForm();
        session()->flash('status', 'تم حفظ الطرف بنجاح.');
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->canManage(), 403);
        Party::findOrFail($id)->delete();
        app(DashboardStatsService::class)->forget((int) auth()->user()->tenant_id);
        session()->flash('status', 'تم حذف الطرف.');
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId', 'type', 'name', 'vat_number', 'cr_number', 'building_number',
            'street', 'district', 'city', 'postal_code', 'email', 'phone', 'showForm',
        ]);
        $this->type = 'customer';
    }

    public function render()
    {
        $parties = Party::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->latest('id')
            ->paginate(10);

        return view('livewire.catalog.parties-index', compact('parties'))
            ->title('العملاء والموردون');
    }
}
