<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header
        title="المنتجات والخدمات"
        description="كتالوج المنتجات المستخدم في بنود الفواتير."
    >
        <x-slot name="actions">
            <x-primary-button type="button" wire:click="create">
                <x-icon name="plus" class="h-4 w-4" />
                إضافة منتج
            </x-primary-button>
        </x-slot>
    </x-page-header>

    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    @if ($showForm)
        <x-ui-card>
            <form wire:submit="save" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <x-input-label value="الاسم عربي" />
                    <x-text-input wire:model="name_ar" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="رمز الصنف" />
                    <x-text-input wire:model="sku" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="السعر" />
                    <x-text-input wire:model="price" type="number" step="0.01" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="فئة الضريبة" />
                    <select wire:model="tax_category" class="ui-select mt-1 w-full">
                        <option value="S">خاضع</option>
                        <option value="Z">صفرية</option>
                        <option value="E">معفى</option>
                        <option value="O">خارج النطاق</option>
                    </select>
                </div>
                <div>
                    <x-input-label value="نسبة الضريبة" />
                    <x-text-input wire:model="tax_rate" type="number" step="0.01" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="وحدة القياس" />
                    <x-text-input wire:model="unit_code" class="mt-1 w-full" />
                </div>
                <div class="md:col-span-3">
                    <x-primary-button>حفظ</x-primary-button>
                </div>
            </form>
        </x-ui-card>
    @endif

    <x-data-table search search-placeholder="بحث بالاسم أو رمز الصنف...">
        <table class="data-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>رمز الصنف</th>
                    <th>السعر</th>
                    <th>الضريبة</th>
                    <th class="col-actions">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr wire:key="product-{{ $product->id }}">
                        <td class="font-medium text-slate-900">{{ $product->name_ar }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>{{ number_format($product->price, 2) }}</td>
                        <td>
                            <x-enum-label type="taxCategory" :value="$product->tax_category" />
                            / {{ $product->tax_rate }}%
                        </td>
                        <td class="col-actions">
                            <div>
                                <x-icon-button icon="edit" label="تعديل" variant="edit" wire:click="edit({{ $product->id }})" />
                                <x-icon-button icon="delete" label="حذف" variant="delete" wire:click="delete({{ $product->id }})" wire:confirm="حذف المنتج؟" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="data-table-empty">لا توجد منتجات مطابقة.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-slot name="footer">
            {{ $products->links() }}
        </x-slot>
    </x-data-table>
</div>
