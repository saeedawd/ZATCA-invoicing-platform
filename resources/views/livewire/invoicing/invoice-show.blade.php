<div class="mx-auto max-w-5xl space-y-6">
    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    <x-page-header
        :title="$invoice->invoice_number ?: 'مسودة'"
        :description="\App\Support\Labels::direction($invoice->direction) . ' / ' . \App\Support\Labels::invoiceType($invoice->invoice_type) . ' / ' . \App\Support\Labels::documentType($invoice->document_type_code)"
    >
        <x-slot name="actions">
            @if ($invoice->isDraft())
                <a href="{{ route($invoice->direction === 'purchase' ? 'purchases.edit' : 'invoices.edit', $invoice) }}" wire:navigate>
                    <x-secondary-button type="button">
                        <x-icon name="edit" class="h-4 w-4" />
                        تعديل
                    </x-secondary-button>
                </a>
            @endif
            @if ($invoice->status === 'issued')
                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank">
                    <x-secondary-button type="button">تحميل ملف الفاتورة</x-secondary-button>
                </a>
                @if (auth()->user()->canManage() && filled($invoice->party?->email))
                    <x-secondary-button type="button" wire:click="emailDocument" wire:confirm="إرسال الفاتورة إلى {{ $invoice->party->email }}؟">
                        إرسال بالبريد
                    </x-secondary-button>
                @endif
            @endif
            @if ($invoice->isSales() && $invoice->status === 'issued' && ! in_array($invoice->zatca_status, ['cleared', 'reported']))
                <x-primary-button type="button" wire:click="retryZatca">إعادة الإرسال للزكاة والضريبة</x-primary-button>
            @endif
        </x-slot>
    </x-page-header>

    <x-ui-card>
        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
            <div>
                <div class="text-slate-500">الطرف</div>
                <div class="mt-1 font-semibold text-slate-900">
                    @if ($invoice->party)
                        <a href="{{ route('parties.statement', $invoice->party) }}" class="text-sky-700 hover:underline" wire:navigate>
                            {{ $invoice->party->name }}
                        </a>
                    @else
                        -
                    @endif
                </div>
            </div>
            <div>
                <div class="text-slate-500">التاريخ</div>
                <div class="mt-1 font-semibold text-slate-900">{{ optional($invoice->issue_date)->format('Y-m-d') }}</div>
            </div>
            <div>
                <div class="text-slate-500">حالة الزكاة والضريبة</div>
                <div class="mt-1"><x-status-badge :status="$invoice->zatca_status" type="zatca" /></div>
            </div>
            <div>
                <div class="text-slate-500">قبل الضريبة</div>
                <div class="mt-1 font-semibold text-slate-900">{{ number_format($invoice->taxable_amount, 2) }}</div>
            </div>
            <div>
                <div class="text-slate-500">الضريبة</div>
                <div class="mt-1 font-semibold text-slate-900">{{ number_format($invoice->tax_amount, 2) }}</div>
            </div>
            <div>
                <div class="text-slate-500">الإجمالي</div>
                <div class="mt-1 font-semibold text-slate-900">{{ number_format($invoice->total_amount, 2) }}</div>
            </div>
            @if ($invoice->status === 'issued')
                <div>
                    <div class="text-slate-500">المدفوع</div>
                    <div class="mt-1 font-semibold text-emerald-700">{{ number_format($invoice->amount_paid, 2) }}</div>
                </div>
                <div>
                    <div class="text-slate-500">المتبقي</div>
                    <div class="mt-1 font-semibold text-rose-700">{{ number_format($invoice->balance_due, 2) }}</div>
                </div>
                <div>
                    <div class="text-slate-500">حالة السداد</div>
                    <div class="mt-1"><x-status-badge :status="$invoice->payment_status" type="payment" /></div>
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
                @foreach ($invoice->lines as $line)
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

    @if ($invoice->status === 'issued')
        <x-ui-card>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-semibold text-slate-900">المدفوعات</h3>
                <x-status-badge :status="$invoice->payment_status" type="payment" />
            </div>

            @if (auth()->user()->canManage() && ! $invoice->isFullyPaid())
                <form wire:submit="recordPayment" class="mb-6 grid grid-cols-1 gap-4 border-b border-slate-100 pb-6 md:grid-cols-6">
                    <div class="md:col-span-1">
                        <x-input-label value="المبلغ" />
                        <x-text-input type="number" step="0.01" min="0.01" wire:model="payment_amount" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('payment_amount')" />
                    </div>
                    <div class="md:col-span-1">
                        <x-input-label value="طريقة الدفع" />
                        <select wire:model="payment_method" class="ui-select mt-1 w-full">
                            <option value="cash">نقدي</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="card">بطاقة</option>
                            <option value="cheque">شيك</option>
                        </select>
                        <x-input-error :messages="$errors->get('payment_method')" />
                    </div>
                    <div class="md:col-span-1">
                        <x-input-label value="التاريخ" />
                        <x-text-input type="date" wire:model="payment_paid_at" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('payment_paid_at')" />
                    </div>
                    <div class="md:col-span-1">
                        <x-input-label value="المرجع" />
                        <x-text-input wire:model="payment_reference" class="mt-1 w-full" placeholder="رقم تحويل / شيك" />
                    </div>
                    <div class="md:col-span-1">
                        <x-input-label value="ملاحظة" />
                        <x-text-input wire:model="payment_notes" class="mt-1 w-full" />
                    </div>
                    <div class="flex items-end md:col-span-1">
                        <x-primary-button class="w-full">تسجيل دفعة</x-primary-button>
                    </div>
                </form>
            @endif

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>الطريقة</th>
                            <th>المبلغ</th>
                            <th>المرجع</th>
                            <th>بواسطة</th>
                            <th class="col-actions">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->payments as $payment)
                            <tr wire:key="payment-{{ $payment->id }}">
                                <td>{{ optional($payment->paid_at)->format('Y-m-d') }}</td>
                                <td><x-enum-label type="paymentMethod" :value="$payment->method" /></td>
                                <td class="font-medium">{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->reference ?: '—' }}</td>
                                <td>{{ $payment->creator?->name ?: '—' }}</td>
                                <td class="col-actions">
                                    @if (auth()->user()->canManage())
                                        <x-icon-button
                                            icon="delete"
                                            label="حذف"
                                            variant="delete"
                                            wire:click="deletePayment({{ $payment->id }})"
                                            wire:confirm="حذف هذه الدفعة؟"
                                        />
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="data-table-empty">لا توجد دفعات مسجّلة بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui-card>
    @endif

    @if ($invoice->submissions->isNotEmpty())
        <x-ui-card :padding="false">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="font-semibold text-slate-900">سجل الإرسال لهيئة الزكاة والضريبة والجمارك</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($invoice->submissions as $submission)
                    <div class="flex flex-wrap items-center gap-3 px-5 py-3.5 text-sm">
                        <x-status-badge :status="$submission->status" type="zatca" />
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-slate-800"><x-enum-label type="submissionType" :value="$submission->type" /></div>
                            <div class="text-slate-500">{{ optional($submission->submitted_at)->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui-card>
    @endif
</div>
