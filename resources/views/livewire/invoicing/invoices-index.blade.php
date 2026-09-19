<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header
        :title="$direction === 'purchase' ? 'فواتير المشتريات' : 'فواتير المبيعات'"
        :description="$direction === 'purchase' ? 'إدارة فواتير المشتريات وتتبع حالتها.' : 'إصدار ومتابعة فواتير المبيعات وإرسالها لهيئة الزكاة والضريبة والجمارك.'"
    >
        <x-slot name="actions">
            <a href="{{ route($direction === 'purchase' ? 'purchases.create' : 'invoices.create') }}" wire:navigate>
                <x-primary-button type="button">
                    <x-icon name="plus" class="h-4 w-4" />
                    فاتورة جديدة
                </x-primary-button>
            </a>
        </x-slot>
    </x-page-header>

    <x-data-table search search-placeholder="بحث برقم الفاتورة...">
        <x-slot name="filters">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">الحالة</label>
                <select wire:model.live="status" class="ui-select">
                    <option value="">كل الحالات</option>
                    <option value="draft">مسودة</option>
                    <option value="issued">صادرة</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">السداد</label>
                <select wire:model.live="paymentStatus" class="ui-select">
                    <option value="">كل حالات السداد</option>
                    <option value="unpaid">غير مدفوعة</option>
                    <option value="partial">مدفوعة جزئياً</option>
                    <option value="paid">مدفوعة</option>
                </select>
            </div>
        </x-slot>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>الطرف</th>
                    <th>النوع</th>
                    <th>الإجمالي</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                    <th>السداد</th>
                    <th>الزكاة والضريبة</th>
                    <th class="col-actions">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr wire:key="invoice-{{ $invoice->id }}">
                        <td class="font-medium text-slate-900">{{ $invoice->invoice_number ?: 'مسودة' }}</td>
                        <td>{{ $invoice->party?->name ?: '-' }}</td>
                        <td>
                            <x-enum-label type="invoiceType" :value="$invoice->invoice_type" />
                            /
                            <x-enum-label type="documentType" :value="$invoice->document_type_code" />
                        </td>
                        <td class="font-medium">{{ number_format($invoice->total_amount, 2) }}</td>
                        <td class="font-medium">
                            @if ($invoice->status === 'issued')
                                {{ number_format($invoice->balance_due, 2) }}
                            @else
                                —
                            @endif
                        </td>
                        <td><x-status-badge :status="$invoice->status" /></td>
                        <td>
                            @if ($invoice->status === 'issued')
                                <x-status-badge :status="$invoice->payment_status" type="payment" />
                            @else
                                —
                            @endif
                        </td>
                        <td><x-status-badge :status="$invoice->zatca_status" type="zatca" /></td>
                        <td class="col-actions">
                            <div>
                                <x-icon-button
                                    icon="view"
                                    label="عرض"
                                    variant="view"
                                    :href="route($direction === 'purchase' ? 'purchases.show' : 'invoices.show', $invoice)"
                                    wire:navigate
                                />
                                @if ($invoice->isDraft())
                                    <x-icon-button
                                        icon="edit"
                                        label="تعديل"
                                        variant="edit"
                                        :href="route($direction === 'purchase' ? 'purchases.edit' : 'invoices.edit', $invoice)"
                                        wire:navigate
                                    />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="data-table-empty">لا توجد فواتير مطابقة.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-slot name="footer">
            {{ $invoices->links() }}
        </x-slot>
    </x-data-table>
</div>
