<?php

namespace App\Livewire\Catalog;

use App\Domains\Catalog\Models\Product;
use App\Domains\Dashboard\Services\DashboardStatsService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProductsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $sku = '';

    public string $name_ar = '';

    public string $name_en = '';

    public string $unit_code = 'PCE';

    public string $price = '0';

    public string $tax_category = 'S';

    public string $tax_rate = '15';

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingId = $product->id;
        $this->sku = (string) $product->sku;
        $this->name_ar = $product->name_ar;
        $this->name_en = (string) $product->name_en;
        $this->unit_code = $product->unit_code;
        $this->price = (string) $product->price;
        $this->tax_category = $product->tax_category;
        $this->tax_rate = (string) $product->tax_rate;
        $this->showForm = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $validated = $this->validate([
            'sku' => ['nullable', 'string', 'max:50'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'unit_code' => ['required', 'string', 'max:10'],
            'price' => ['required', 'numeric', 'min:0'],
            'tax_category' => ['required', 'in:S,Z,E,O'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $payload = $validated + [
            'tenant_id' => auth()->user()->tenant_id,
            'is_active' => true,
        ];

        if ($this->editingId) {
            Product::findOrFail($this->editingId)->update($payload);
        } else {
            Product::create($payload);
        }

        app(DashboardStatsService::class)->forget((int) auth()->user()->tenant_id);
        $this->resetForm();
        session()->flash('status', 'تم حفظ المنتج بنجاح.');
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->canManage(), 403);
        Product::findOrFail($id)->delete();
        app(DashboardStatsService::class)->forget((int) auth()->user()->tenant_id);
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'sku', 'name_ar', 'name_en', 'showForm']);
        $this->unit_code = 'PCE';
        $this->price = '0';
        $this->tax_category = 'S';
        $this->tax_rate = '15';
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn ($q) => $q->where('name_ar', 'like', '%'.$this->search.'%'))
            ->latest('id')
            ->paginate(10);

        return view('livewire.catalog.products-index', compact('products'))
            ->title('المنتجات والخدمات');
    }
}
