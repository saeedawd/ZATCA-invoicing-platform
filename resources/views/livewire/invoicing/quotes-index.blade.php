<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header
        title="عروض الأسعار"
        description="أنشئ عروض أسعار للعملاء ثم حوّلها إلى فواتير عند الموافقة."
    >
        <x-slot name="actions">
            <a href="{{ route('quotes.create') }}" wire:navigate>
                <x-primary-button type="button">
                    <x-icon name="plus" class="h-4 w-4" />
                    عرض سعر جديد
                </x-primary-button>
            </a>
        </x-slot>
    </x-page-header>

    <x-data-table search search-placeholder="بحث برقم العرض...">
        <x-slot name="filters">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">الحالة</label>
                <select wire:model.live="status" class="ui-select">
                    <option value="">كل الحالات</option>
                    <option value="draft">مسودة</option>
                    <option value="sent">مُرسل</option>
                    <option value="accepted">مقبول</option>
                    <option value="rejected">مرفوض</option>
                    <option value="converted">محوّل لفاتورة</option>
                </select>
            </div>
        </x-slot>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>العميل</th>
                    <th>التاريخ</th>
                    <th>صالح حتى</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th class="col-actions">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotes as $quote)
                    <tr wire:key="quote-{{ $quote->id }}">
                        <td class="font-medium text-slate-900">{{ $quote->quote_number ?: 'مسودة' }}</td>
                        <td>{{ $quote->party?->name ?: '-' }}</td>
                        <td>{{ optional($quote->issue_date)->format('Y-m-d') }}</td>
                        <td>{{ optional($quote->valid_until)->format('Y-m-d') ?: '—' }}</td>
                        <td class="font-medium">{{ number_format($quote->total_amount, 2) }}</td>
                        <td><x-status-badge :status="$quote->status" type="quote" /></td>
                        <td class="col-actions">
                            <div>
                                <x-icon-button
                                    icon="view"
                                    label="عرض"
                                    variant="view"
                                    :href="route('quotes.show', $quote)"
                                    wire:navigate
                                />
                                @if ($quote->isEditable())
                                    <x-icon-button
                                        icon="edit"
                                        label="تعديل"
                                        variant="edit"
                                        :href="route('quotes.edit', $quote)"
                                        wire:navigate
                                    />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="data-table-empty">لا توجد عروض أسعار مطابقة.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-slot name="footer">
            {{ $quotes->links() }}
        </x-slot>
    </x-data-table>
</div>
