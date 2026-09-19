<?php

namespace App\Domains\Invoicing\Services;

class InvoiceCalculator
{
    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array{lines: array<int, array<string, mixed>>, tax_totals: array<int, array<string, mixed>>, totals: array<string, float>}
     */
    public function calculate(array $lines, float $documentDiscount = 0): array
    {
        $computedLines = [];
        $taxBuckets = [];
        $lineExtension = 0.0;

        foreach (array_values($lines) as $index => $line) {
            $qty = (float) ($line['quantity'] ?? 0);
            $price = (float) ($line['unit_price'] ?? 0);
            $discount = (float) ($line['discount'] ?? 0);
            $taxRate = (float) ($line['tax_rate'] ?? 15);
            $taxCategory = (string) ($line['tax_category'] ?? 'S');

            $net = round(($qty * $price) - $discount, 2);
            $tax = round($net * ($taxRate / 100), 2);
            $total = round($net + $tax, 2);
            $lineExtension += $net;

            $computedLines[] = array_merge($line, [
                'line_no' => $index + 1,
                'quantity' => $qty,
                'unit_price' => $price,
                'discount' => $discount,
                'tax_rate' => $taxRate,
                'tax_category' => $taxCategory,
                'unit_code' => $line['unit_code'] ?? 'PCE',
                'line_net' => $net,
                'line_tax' => $tax,
                'line_total' => $total,
            ]);

            $bucketKey = $taxCategory.'|'.$taxRate;
            if (! isset($taxBuckets[$bucketKey])) {
                $taxBuckets[$bucketKey] = [
                    'tax_category' => $taxCategory,
                    'tax_rate' => $taxRate,
                    'taxable_amount' => 0.0,
                    'tax_amount' => 0.0,
                ];
            }
            $taxBuckets[$bucketKey]['taxable_amount'] += $net;
            $taxBuckets[$bucketKey]['tax_amount'] += $tax;
        }

        $documentDiscount = round($documentDiscount, 2);
        $taxable = round(max($lineExtension - $documentDiscount, 0), 2);
        $taxAmount = round(array_sum(array_column($taxBuckets, 'tax_amount')), 2);

        if ($documentDiscount > 0 && $lineExtension > 0) {
            $ratio = $taxable / $lineExtension;
            $taxAmount = 0.0;
            foreach ($taxBuckets as &$bucket) {
                $bucket['taxable_amount'] = round($bucket['taxable_amount'] * $ratio, 2);
                $bucket['tax_amount'] = round($bucket['taxable_amount'] * ($bucket['tax_rate'] / 100), 2);
                $taxAmount += $bucket['tax_amount'];
            }
            unset($bucket);
            $taxAmount = round($taxAmount, 2);
        }

        $total = round($taxable + $taxAmount, 2);

        return [
            'lines' => $computedLines,
            'tax_totals' => array_values(array_map(function (array $bucket) {
                $bucket['taxable_amount'] = round($bucket['taxable_amount'], 2);
                $bucket['tax_amount'] = round($bucket['tax_amount'], 2);

                return $bucket;
            }, $taxBuckets)),
            'totals' => [
                'line_extension_amount' => round($lineExtension, 2),
                'discount_amount' => $documentDiscount,
                'taxable_amount' => $taxable,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
                'prepaid_amount' => 0.0,
                'payable_amount' => $total,
            ],
        ];
    }
}
