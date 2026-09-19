<div class="mx-auto max-w-6xl space-y-6">
    <x-page-header
        title="تقارير الضريبة والنشاط"
        description="ملخص المبيعات والمشتريات وحالات الإرسال لهيئة الزكاة والضريبة والجمارك خلال الفترة المحددة."
    />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-stat-card
            label="مبيعات خاضعة"
            :value="number_format($salesTaxable, 2)"
            :hint="'ضريبة: ' . number_format($salesTax, 2) . ' | إجمالي: ' . number_format($salesTotal, 2)"
        />
        <x-stat-card
            label="مشتريات"
            :value="number_format($purchaseTaxable, 2)"
            :hint="'ضريبة: ' . number_format($purchaseTax, 2) . ' | إجمالي: ' . number_format($purchaseTotal, 2)"
        />
        <x-ui-card>
            <div class="text-sm font-medium text-slate-500">حالات الإرسال</div>
            <div class="mt-3 space-y-2">
                @forelse ($byZatcaStatus as $status => $total)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <x-status-badge :status="$status" type="zatca" />
                        <span class="font-semibold text-slate-800">{{ $total }}</span>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">لا بيانات</div>
                @endforelse
            </div>
        </x-ui-card>
    </div>

    <x-data-table>
        <x-slot name="filters">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">من</label>
                <x-text-input type="date" wire:model.live="from" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">إلى</label>
                <x-text-input type="date" wire:model.live="to" />
            </div>
        </x-slot>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الإجراء</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($auditLogs as $log)
                    <tr>
                        <td class="font-medium text-slate-900"><x-enum-label type="auditAction" :value="$log->action" /></td>
                        <td class="text-slate-500">{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="data-table-empty">لا يوجد سجل بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-data-table>
</div>
