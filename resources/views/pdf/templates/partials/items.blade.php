<table class="items">
    <thead>
        <tr>
            <th width="14%" class="center">الإجمالي</th>
            <th width="10%" class="center">الضريبة</th>
            <th width="14%" class="center">السعر</th>
            <th width="12%" class="center">الكمية</th>
            <th width="44%" class="ar">الوصف</th>
            <th width="6%" class="center">م</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($invoice->lines as $line)
            <tr>
                <td class="center middle bold">{{ number_format((float) $line->line_total, 2) }}</td>
                <td class="center middle">{{ number_format((float) $line->tax_rate, 0) }}%</td>
                <td class="center middle">{{ number_format((float) $line->unit_price, 2) }}</td>
                <td class="center middle">{{ rtrim(rtrim(number_format((float) $line->quantity, 3, '.', ''), '0'), '.') }}</td>
                <td class="ar middle bold">{{ $line->description }}</td>
                <td class="center middle">{{ $line->line_no }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
