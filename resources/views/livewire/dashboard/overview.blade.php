<div class="mx-auto max-w-7xl space-y-6">
    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    @if (! $organization?->isProfileComplete())
        <div class="alert-warning">
            أكمل
            <a href="{{ route('organization.edit') }}" class="font-semibold text-amber-950 underline underline-offset-2" wire:navigate>بيانات المنشأة</a>
            قبل إصدار الفواتير الإلكترونية.
        </div>
    @endif

    <x-page-header
        title="لوحة التحكم"
        description="نظرة سريعة على المبيعات والمشتريات وحالة الربط مع هيئة الزكاة والضريبة والجمارك."
    >
        <x-slot name="actions">
            <a href="{{ route('invoices.create') }}" wire:navigate>
                <x-primary-button type="button">
                    <x-icon name="plus" class="h-4 w-4" />
                    فاتورة مبيعات
                </x-primary-button>
            </a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-stat-card
            label="مبيعات مصدرة"
            :value="number_format($salesCount)"
            :hint="number_format($salesTotal, 2) . ' ر.س'"
        />
        <x-stat-card
            label="ضريبة المبيعات"
            :value="number_format($salesTax, 2) . ' ر.س'"
        />
        <x-stat-card
            label="مشتريات"
            :value="number_format($purchaseCount)"
            :hint="number_format($purchaseTotal, 2) . ' ر.س'"
        />
        <x-stat-card label="حالة الزكاة والضريبة" :value="$zatcaAccepted" hint="مقبولة">
            <x-slot name="footer">
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span class="text-rose-600">مرفوضة: {{ $zatcaRejected }}</span>
                    <span class="text-slate-400">|</span>
                    <span class="text-slate-500">الجهاز: {{ $device?->device_serial ?: 'غير مربوط' }}</span>
                </div>
            </x-slot>
        </x-stat-card>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-stat-card label="العملاء والموردون" :value="number_format($partiesCount)" />
        <x-stat-card label="المنتجات" :value="number_format($productsCount)" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui-card :padding="false">
            <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">آخر الفواتير</h2>
                <a href="{{ route('invoices.index') }}" class="btn-ghost" wire:navigate>عرض الكل</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentInvoices as $invoice)
                    <div class="flex items-center gap-3 px-5 py-3.5">
                        <x-icon-button
                            icon="view"
                            label="عرض الفاتورة"
                            variant="view"
                            :href="route($invoice->direction === 'purchase' ? 'purchases.show' : 'invoices.show', $invoice)"
                            wire:navigate
                        />
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-slate-800">
                                {{ $invoice->invoice_number ?: 'مسودة' }}
                                <span class="font-normal text-slate-400">—</span>
                                {{ $invoice->party?->name ?: 'بدون طرف' }}
                            </div>
                            <div class="mt-1 flex items-center gap-2">
                                <x-status-badge :status="$invoice->status" />
                                <span class="text-xs text-slate-500">{{ number_format($invoice->total_amount, 2) }} ر.س</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">لا توجد فواتير بعد.</p>
                @endforelse
            </div>
        </x-ui-card>

        <x-ui-card :padding="false">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">آخر إرسالات الزكاة والضريبة</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentSubmissions as $submission)
                    <div class="flex items-center gap-3 px-5 py-3.5">
                        <x-status-badge :status="$submission->status" type="zatca" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-slate-800">
                                {{ $submission->invoice?->invoice_number ?: '—' }}
                                <span class="font-normal text-slate-400">(<x-enum-label type="submissionType" :value="$submission->type" />)</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">لا توجد إرسالات بعد.</p>
                @endforelse
            </div>
        </x-ui-card>
    </div>
</div>
