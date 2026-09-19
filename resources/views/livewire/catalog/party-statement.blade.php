<div class="mx-auto max-w-5xl space-y-6">
    <x-page-header
        :title="'كشف حساب — '.$party->name"
        :description="\App\Support\Labels::partyType($party->type).($party->vat_number ? ' / '.$party->vat_number : '')"
    >
        <x-slot name="actions">
            <a href="{{ route('parties.index') }}" wire:navigate>
                <x-secondary-button type="button">العودة للأطراف</x-secondary-button>
            </a>
        </x-slot>
    </x-page-header>

    <x-ui-card>
        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
            <div>
                <div class="text-slate-500">ذمم مدينة (مستحق لنا)</div>
                <div class="mt-1 text-lg font-semibold text-emerald-700">{{ number_format($receivables, 2) }}</div>
            </div>
            <div>
                <div class="text-slate-500">ذمم دائنة (مستحق عليه)</div>
                <div class="mt-1 text-lg font-semibold text-rose-700">{{ number_format($payables, 2) }}</div>
            </div>
            <div>
                <div class="text-slate-500">صافي الرصيد</div>
                <div @class([
                    'mt-1 text-lg font-semibold',
                    'text-emerald-700' => $balance > 0,
                    'text-rose-700' => $balance < 0,
                    'text-slate-900' => $balance == 0,
                ])>
                    {{ number_format($balance, 2) }}
                    <span class="text-xs font-medium text-slate-500">
                        @if ($balance > 0)
                            (علينا تحصيله)
                        @elseif ($balance < 0)
                            (علينا سداده)
                        @else
                            (متوازن)
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </x-ui-card>

    <x-data-table>
        <table class="data-table">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>البيان</th>
                    <th>مدين</th>
                    <th>دائن</th>
                    <th>الرصيد</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $index => $row)
                    <tr wire:key="stmt-{{ $index }}">
                        <td>{{ $row['date'] }}</td>
                        <td>
                            <a href="{{ $row['href'] }}" class="font-medium text-sky-700 hover:underline" wire:navigate>
                                {{ $row['description'] }}
                            </a>
                        </td>
                        <td>{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                        <td>{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                        <td class="font-medium">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="data-table-empty">لا توجد حركات على هذا الطرف بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-data-table>
</div>
