<?php

namespace App\Livewire\Catalog;

use App\Domains\Catalog\Models\Party;
use App\Domains\Invoicing\Models\Invoice;
use App\Support\Labels;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PartyStatement extends Component
{
    public Party $party;

    public function mount(Party $party): void
    {
        $this->party = $party;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, balance: float, receivables: float, payables: float}
     */
    protected function buildStatement(): array
    {
        $invoices = Invoice::query()
            ->with('payments')
            ->where('party_id', $this->party->id)
            ->where('status', 'issued')
            ->orderBy('issue_date')
            ->orderBy('id')
            ->get();

        $events = [];

        foreach ($invoices as $invoice) {
            $isSales = $invoice->direction === 'sales';
            $total = round((float) $invoice->total_amount, 2);
            $showRoute = $isSales ? 'invoices.show' : 'purchases.show';

            $events[] = [
                'sort_date' => optional($invoice->issue_date)->format('Y-m-d').'-'.$invoice->id.'-0',
                'date' => optional($invoice->issue_date)->format('Y-m-d'),
                'description' => trim(
                    Labels::documentType($invoice->document_type_code)
                    .' '.$invoice->invoice_number
                    .' ('.Labels::direction($invoice->direction).')'
                ),
                'debit' => $isSales ? $total : 0.0,
                'credit' => $isSales ? 0.0 : $total,
                'href' => route($showRoute, $invoice),
            ];

            foreach ($invoice->payments as $payment) {
                $amount = round((float) $payment->amount, 2);
                $events[] = [
                    'sort_date' => optional($payment->paid_at)->format('Y-m-d').'-'.$invoice->id.'-'.$payment->id,
                    'date' => optional($payment->paid_at)->format('Y-m-d'),
                    'description' => 'دفعة '.Labels::paymentMethod($payment->method)
                        .' على '.$invoice->invoice_number
                        .($payment->reference ? ' — '.$payment->reference : ''),
                    'debit' => $isSales ? 0.0 : $amount,
                    'credit' => $isSales ? $amount : 0.0,
                    'href' => route($showRoute, $invoice),
                ];
            }
        }

        usort($events, fn ($a, $b) => strcmp($a['sort_date'], $b['sort_date']));

        $balance = 0.0;
        $receivables = 0.0;
        $payables = 0.0;
        $rows = [];

        foreach ($events as $event) {
            $balance = round($balance + $event['debit'] - $event['credit'], 2);
            $rows[] = [
                'date' => $event['date'],
                'description' => $event['description'],
                'debit' => $event['debit'],
                'credit' => $event['credit'],
                'balance' => $balance,
                'href' => $event['href'],
            ];
        }

        foreach ($invoices as $invoice) {
            $due = round((float) $invoice->balance_due, 2);
            if ($due <= 0) {
                continue;
            }
            if ($invoice->direction === 'sales') {
                $receivables = round($receivables + $due, 2);
            } else {
                $payables = round($payables + $due, 2);
            }
        }

        return [
            'rows' => $rows,
            'balance' => $balance,
            'receivables' => $receivables,
            'payables' => $payables,
        ];
    }

    public function render()
    {
        $statement = $this->buildStatement();

        return view('livewire.catalog.party-statement', [
            'rows' => $statement['rows'],
            'balance' => $statement['balance'],
            'receivables' => $statement['receivables'],
            'payables' => $statement['payables'],
        ])->title('كشف حساب — '.$this->party->name);
    }
}
