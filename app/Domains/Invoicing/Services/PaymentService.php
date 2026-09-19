<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PaymentService
{
    /**
     * @param  array{amount: float|int|string, method: string, paid_at?: string, reference?: ?string, notes?: ?string}  $data
     */
    public function record(Invoice $invoice, User $user, array $data): InvoicePayment
    {
        if ($invoice->status !== 'issued') {
            throw new RuntimeException('لا يمكن تسجيل دفعة إلا على فاتورة صادرة.');
        }

        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('مبلغ الدفعة يجب أن يكون أكبر من صفر.');
        }

        $method = (string) ($data['method'] ?? '');
        if (! in_array($method, InvoicePayment::METHODS, true)) {
            throw new InvalidArgumentException('طريقة الدفع غير صالحة.');
        }

        return DB::transaction(function () use ($invoice, $user, $data, $amount, $method) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);

            $balance = round((float) $invoice->balance_due, 2);
            if ($amount - $balance > 0.009) {
                throw new InvalidArgumentException('مبلغ الدفعة يتجاوز الرصيد المتبقي ('.$balance.').');
            }

            $payment = InvoicePayment::create([
                'tenant_id' => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => $method,
                'paid_at' => $data['paid_at'] ?? now()->toDateString(),
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->recalculate($invoice);

            return $payment;
        });
    }

    public function delete(InvoicePayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($payment->invoice_id);
            $payment->delete();
            $this->recalculate($invoice);
        });
    }

    public function recalculate(Invoice $invoice): Invoice
    {
        $amountPaid = round((float) $invoice->payments()->sum('amount'), 2);
        $total = round((float) $invoice->total_amount, 2);
        $balanceDue = round(max(0, $total - $amountPaid), 2);

        $paymentStatus = match (true) {
            $amountPaid <= 0 => 'unpaid',
            $balanceDue <= 0 => 'paid',
            default => 'partial',
        };

        $invoice->forceFill([
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
            'payment_status' => $paymentStatus,
        ])->save();

        return $invoice->refresh();
    }

    public function syncBalanceFromTotal(Invoice $invoice): void
    {
        $amountPaid = round((float) $invoice->amount_paid, 2);
        $total = round((float) $invoice->total_amount, 2);
        $balanceDue = round(max(0, $total - $amountPaid), 2);

        $paymentStatus = match (true) {
            $amountPaid <= 0 => 'unpaid',
            $balanceDue <= 0 => 'paid',
            default => 'partial',
        };

        $invoice->forceFill([
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
            'payment_status' => $paymentStatus,
        ])->save();
    }
}
