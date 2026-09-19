<div class="mx-auto max-w-5xl space-y-6">
    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    <x-page-header
        :title="$quote->quote_number ?: 'مسودة عرض سعر'"
        description="عرض سعر للعميل مع إمكانية القبول والتحويل إلى فاتورة."
    >
        <x-slot name="actions">
            @if ($quote->isEditable())
                <a href="{{ route('quotes.edit', $quote) }}" wire:navigate>
                    <x-secondary-button type="button">
                        <x-icon name="edit" class="h-4 w-4" />
                        تعديل
                    </x-secondary-button>
                </a>
                <x-primary-button type="button" wire:click="send">إرسال العرض</x-primary-button>
            @endif
            @if (in_array($quote->status, ['sent', 'accepted', 'rejected', 'converted'], true))
                <x-secondary-button type="button" wire:click="downloadPdf">تحميل PDF</x-secondary-button>
                @if (auth()->user()->canManage() && filled($quote->party?->email))
                    <x-secondary-button type="button" wire:click="emailDocument" wire:confirm="إرسال عرض السعر إلى {{ $quote->party->email }}؟">
                        إرسال بالبريد
                    </x-secondary-button>
                @endif
            @endif
            @if ($quote->canAccept())
                <x-primary-button type="button" wire:click="accept">قبول</x-primary-button>
            @endif
            @if ($quote->canReject())
                <x-secondary-button type="button" wire:click="reject" wire:confirm="رفض عرض السعر؟">رفض</x-secondary-button>
            @endif
            @if ($quote->canConvert())
                <x-primary-button type="button" wire:click="convertToInvoice">تحويل إلى فاتورة</x-primary-button>
            @endif
        </x-slot>
    </x-page-header>

    <x-ui-card>
        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
            <div>
                <div class="text-slate-500">العميل</div>
                <div class="mt-1 font-semibold text-slate-900">{{ $quote->party?->name ?: '-' }}</div>
            </div>
            <div>
                <div class="text-slate-500">تاريخ العرض</div>
                <div class="mt-1 font-semibold text-slate-900">{{ optional($quote->issue_date)->format('Y-m-d') }}</div>
            </div>
            <div>
                <div class="text-slate-500">الحالة</div>
                <div class="mt-1"><x-status-badge :status="$quote->status" type="quote" /></div>
            </div>
            <div>
                <div class="text-slate-500">صالح حتى</div>
                <div class="mt-1 font-semibold text-slate-900">{{ optional($quote->valid_until)->format('Y-m-d') ?: '—' }}</div>
            </div>
            <div>
                <div class="text-slate-500">قبل الضريبة</div>
                <div class="mt-1 font-semibold text-slate-900">{{ number_format($quote->taxable_amount, 2) }}</div>
            </div>
            <div>
                <div class="text-slate-500">الإجمالي</div>
                <div class="mt-1 font-semibold text-slate-900">{{ number_format($quote->total_amount, 2) }}</div>
            </div>
            @if ($quote->convertedInvoice)
                <div class="md:col-span-3">
                    <div class="text-slate-500">الفاتورة الناتجة</div>
                    <div class="mt-1">
                        <a href="{{ route('invoices.show', $quote->convertedInvoice) }}" class="font-semibold text-sky-700 hover:underline" wire:navigate>
                            {{ $quote->convertedInvoice->invoice_number ?: 'مسودة فاتورة #'.$quote->convertedInvoice->id }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </x-ui-card>

    <x-data-table>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الوصف</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>الصافي</th>
                    <th>الضريبة</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quote->lines as $line)
                    <tr>
                        <td>{{ $line->line_no }}</td>
                        <td class="font-medium text-slate-900">{{ $line->description }}</td>
                        <td>{{ $line->quantity }}</td>
                        <td>{{ number_format($line->unit_price, 2) }}</td>
                        <td>{{ number_format($line->line_net, 2) }}</td>
                        <td>{{ number_format($line->line_tax, 2) }}</td>
                        <td class="font-medium">{{ number_format($line->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-data-table>

    @if ($quote->note)
        <x-ui-card>
            <h3 class="mb-2 font-semibold text-slate-900">ملاحظات</h3>
            <p class="text-sm text-slate-600 whitespace-pre-line">{{ $quote->note }}</p>
        </x-ui-card>
    @endif
</div>
