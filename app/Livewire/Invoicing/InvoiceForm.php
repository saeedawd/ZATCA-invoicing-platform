<?php

namespace App\Livewire\Invoicing;

use App\Domains\Catalog\Models\Party;
use App\Domains\Catalog\Models\Product;
use App\Domains\Dashboard\Services\DashboardStatsService;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Services\InvoicePdfRenderer;
use App\Domains\Invoicing\Services\InvoiceService;
use App\Domains\Organization\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class InvoiceForm extends Component
{
    public string $direction = 'sales';

    public ?int $invoiceId = null;

    public string $invoice_type = 'simplified';

    public string $document_type_code = '388';

    public ?int $party_id = null;

    public string $issue_date = '';

    public string $note = '';

    public string $discount_amount = '0';

    public ?int $referenced_invoice_id = null;

    public string $reference_reason = '';

    /** @var array<int, array<string, mixed>> */
    public array $lines = [];

    public bool $showQuickParty = false;

    public string $quickPartyName = '';

    public string $quickPartyVat = '';

    public string $quickPartyPhone = '';

    public string $quickPartyCity = '';

    public string $quickPartyEmail = '';

    public bool $showQuickProduct = false;

    public ?int $quickProductLineIndex = null;

    public string $quickProductName = '';

    public string $quickProductSku = '';

    public string $quickProductPrice = '0';

    public string $quickProductTaxRate = '15';

    public string $quickProductTaxCategory = 'S';

    public string $quickProductUnitCode = 'PCE';

    public function mount(?string $direction = null, mixed $invoice = null): void
    {
        if ($invoice !== null && ! $invoice instanceof Invoice) {
            $invoice = Invoice::query()->findOrFail($invoice);
        }

        /** @var Invoice|null $invoice */
        $this->direction = $direction
            ?? ($invoice?->direction)
            ?? (request()->routeIs('purchases.*') ? 'purchase' : 'sales');
        $this->issue_date = now()->toDateString();

        if ($invoice) {
            abort_unless($invoice->direction === $this->direction, 404);
            $invoice->loadMissing(['lines', 'references']);
            $this->invoiceId = $invoice->id;
            $this->invoice_type = $invoice->invoice_type;
            $this->document_type_code = $invoice->document_type_code;
            $this->party_id = $invoice->party_id;
            $this->issue_date = optional($invoice->issue_date)->format('Y-m-d') ?: now()->toDateString();
            $this->note = (string) $invoice->note;
            $this->discount_amount = (string) $invoice->discount_amount;
            $this->lines = $invoice->lines->map(fn ($line) => [
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => (string) $line->quantity,
                'unit_price' => (string) $line->unit_price,
                'discount' => (string) $line->discount,
                'tax_category' => $line->tax_category,
                'tax_rate' => (string) $line->tax_rate,
                'unit_code' => $line->unit_code,
            ])->all();
            $ref = $invoice->references->first();
            $this->referenced_invoice_id = $ref?->referenced_invoice_id;
            $this->reference_reason = (string) $ref?->reason;
        } else {
            $this->addLine();
        }
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'product_id' => null,
            'description' => '',
            'quantity' => '1',
            'unit_price' => '0',
            'discount' => '0',
            'tax_category' => 'S',
            'tax_rate' => '15',
            'unit_code' => 'PCE',
        ];
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function fillFromProduct(int $index): void
    {
        $productId = $this->lines[$index]['product_id'] ?? null;
        if (! $productId) {
            return;
        }

        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $this->lines[$index]['description'] = $product->name_ar;
        $this->lines[$index]['unit_price'] = (string) $product->price;
        $this->lines[$index]['tax_category'] = $product->tax_category;
        $this->lines[$index]['tax_rate'] = (string) $product->tax_rate;
        $this->lines[$index]['unit_code'] = $product->unit_code;
    }

    public function openQuickParty(): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $this->resetQuickParty();
        $this->showQuickParty = true;
        $this->showQuickProduct = false;
    }

    public function closeQuickParty(): void
    {
        $this->showQuickParty = false;
        $this->resetQuickParty();
        $this->resetValidation([
            'quickPartyName',
            'quickPartyVat',
            'quickPartyPhone',
            'quickPartyCity',
        ]);
    }

    public function saveQuickParty(): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $validated = $this->validate([
            'quickPartyName' => ['required', 'string', 'max:255'],
            'quickPartyVat' => ['nullable', 'regex:/^3\d{13}3$/'],
            'quickPartyPhone' => ['nullable', 'string', 'max:30'],
            'quickPartyCity' => ['nullable', 'string', 'max:255'],
            'quickPartyEmail' => ['nullable', 'email', 'max:255'],
        ], [
            'quickPartyName.required' => 'اسم '.($this->direction === 'purchase' ? 'المورد' : 'العميل').' مطلوب.',
            'quickPartyVat.regex' => 'الرقم الضريبي يجب أن يكون 15 رقماً ويبدأ وينتهي بـ 3.',
        ]);

        $party = Party::create([
            'tenant_id' => auth()->user()->tenant_id,
            'type' => $this->direction === 'purchase' ? 'supplier' : 'customer',
            'name' => $validated['quickPartyName'],
            'vat_number' => $validated['quickPartyVat'] ?: null,
            'phone' => $validated['quickPartyPhone'] ?: null,
            'city' => $validated['quickPartyCity'] ?: null,
            'email' => $validated['quickPartyEmail'] ?: null,
            'is_active' => true,
        ]);

        app(DashboardStatsService::class)->forget((int) auth()->user()->tenant_id);

        $this->party_id = $party->id;
        $this->closeQuickParty();
        session()->flash('status', 'تم إضافة '.($this->direction === 'purchase' ? 'المورد' : 'العميل').' واختياره في الفاتورة.');
    }

    public function openQuickProduct(?int $lineIndex = null): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $this->resetQuickProduct();
        $this->quickProductLineIndex = $lineIndex;
        $this->showQuickProduct = true;
        $this->showQuickParty = false;
    }

    public function closeQuickProduct(): void
    {
        $this->showQuickProduct = false;
        $this->resetQuickProduct();
        $this->resetValidation([
            'quickProductName',
            'quickProductSku',
            'quickProductPrice',
            'quickProductTaxRate',
            'quickProductTaxCategory',
            'quickProductUnitCode',
        ]);
    }

    public function saveQuickProduct(): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $validated = $this->validate([
            'quickProductName' => ['required', 'string', 'max:255'],
            'quickProductSku' => ['nullable', 'string', 'max:50'],
            'quickProductPrice' => ['required', 'numeric', 'min:0'],
            'quickProductTaxCategory' => ['required', 'in:S,Z,E,O'],
            'quickProductTaxRate' => ['required', 'numeric', 'min:0', 'max:100'],
            'quickProductUnitCode' => ['required', 'string', 'max:10'],
        ], [
            'quickProductName.required' => 'اسم المنتج مطلوب.',
        ]);

        $product = Product::create([
            'tenant_id' => auth()->user()->tenant_id,
            'sku' => $validated['quickProductSku'] ?: null,
            'name_ar' => $validated['quickProductName'],
            'unit_code' => $validated['quickProductUnitCode'],
            'price' => $validated['quickProductPrice'],
            'tax_category' => $validated['quickProductTaxCategory'],
            'tax_rate' => $validated['quickProductTaxRate'],
            'is_active' => true,
        ]);

        app(DashboardStatsService::class)->forget((int) auth()->user()->tenant_id);

        $index = $this->quickProductLineIndex;
        if ($index === null || ! array_key_exists($index, $this->lines)) {
            $this->addLine();
            $index = array_key_last($this->lines);
        }

        $this->lines[$index]['product_id'] = $product->id;
        $this->fillFromProduct($index);
        $this->closeQuickProduct();
        session()->flash('status', 'تم إضافة المنتج وربطه بالبند.');
    }

    public function getNeedsReferenceProperty(): bool
    {
        return in_array($this->document_type_code, ['381', '383'], true);
    }

    /**
     * @return array{lines_net: float, discount: float, tax: float, total: float}
     */
    public function getTotalsProperty(): array
    {
        $linesNet = 0.0;
        $tax = 0.0;

        foreach ($this->lines as $line) {
            $qty = (float) ($line['quantity'] ?? 0);
            $price = (float) ($line['unit_price'] ?? 0);
            $discount = (float) ($line['discount'] ?? 0);
            $rate = (float) ($line['tax_rate'] ?? 0);
            $net = max(($qty * $price) - $discount, 0);
            $linesNet += $net;
            $tax += $net * ($rate / 100);
        }

        $docDiscount = (float) $this->discount_amount;
        $taxable = max($linesNet - $docDiscount, 0);
        $taxPreview = $linesNet > 0 ? $tax * ($taxable / $linesNet) : 0;

        return [
            'lines_net' => round($linesNet, 2),
            'discount' => round($docDiscount, 2),
            'tax' => round($taxPreview, 2),
            'total' => round($taxable + $taxPreview, 2),
        ];
    }

    public function saveDraft(InvoiceService $service)
    {
        return $this->persist($service, false);
    }

    public function issueInvoice(InvoiceService $service)
    {
        return $this->persist($service, true);
    }

    public function previewInvoice(InvoicePdfRenderer $renderer): StreamedResponse
    {
        abort_unless(auth()->user()->canManage(), 403);

        $this->validate([
            'invoice_type' => ['required', 'in:simplified,standard'],
            'document_type_code' => ['required', 'in:388,381,383'],
            'party_id' => [$this->invoice_type === 'standard' || $this->direction === 'purchase' ? 'required' : 'nullable', 'exists:parties,id'],
            'issue_date' => ['required', 'date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'lines.*.description.required' => 'وصف البند مطلوب قبل المعاينة.',
            'lines.*.quantity.gt' => 'كمية البند يجب أن تكون أكبر من صفر.',
        ]);

        /** @var Organization|null $organization */
        $organization = auth()->user()->tenant->organization;
        abort_unless($organization, 404);

        $party = $this->party_id
            ? Party::query()->find($this->party_id)
            : null;

        $binary = $renderer->previewDraftBinary($organization, [
            'direction' => $this->direction,
            'invoice_type' => $this->invoice_type,
            'document_type_code' => $this->document_type_code,
            'issue_date' => $this->issue_date,
            'note' => $this->note !== '' ? $this->note : 'معاينة فاتورة — لم يتم الإصدار بعد.',
            'discount_amount' => $this->discount_amount,
        ], $this->lines, $party);

        $filename = sprintf(
            'invoice-preview-%s.pdf',
            now()->format('Ymd-His')
        );

        return response()->streamDownload(
            static function () use ($binary): void {
                echo $binary;
            },
            $filename,
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]
        );
    }

    protected function persist(InvoiceService $service, bool $issue)
    {
        abort_unless(auth()->user()->canManage(), 403);

        $this->validate([
            'invoice_type' => ['required', 'in:simplified,standard'],
            'document_type_code' => ['required', 'in:388,381,383'],
            'party_id' => [$this->invoice_type === 'standard' || $this->direction === 'purchase' ? 'required' : 'nullable', 'exists:parties,id'],
            'issue_date' => ['required', 'date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'referenced_invoice_id' => ['nullable', 'exists:invoices,id'],
        ]);

        $payload = [
            'direction' => $this->direction,
            'invoice_type' => $this->invoice_type,
            'document_type_code' => $this->document_type_code,
            'party_id' => $this->party_id,
            'issue_date' => $this->issue_date,
            'note' => $this->note,
            'discount_amount' => $this->discount_amount,
            'referenced_invoice_id' => $this->referenced_invoice_id,
            'reference_reason' => $this->reference_reason,
        ];

        if ($this->invoiceId) {
            $invoice = Invoice::findOrFail($this->invoiceId);
            $invoice = $service->updateDraft($invoice, auth()->user(), $payload, $this->lines);
        } else {
            $invoice = $service->createDraft(auth()->user(), $payload, $this->lines);
            $this->invoiceId = $invoice->id;
        }

        if ($issue) {
            $service->issue($invoice, auth()->user());
            session()->flash('status', 'تم إصدار الفاتورة وإرسالها للطابور.');

            return $this->redirect(route($this->direction === 'purchase' ? 'purchases.show' : 'invoices.show', $invoice), navigate: true);
        }

        session()->flash('status', 'تم حفظ المسودة.');

        return $this->redirect(route($this->direction === 'purchase' ? 'purchases.edit' : 'invoices.edit', $invoice), navigate: true);
    }

    protected function resetQuickParty(): void
    {
        $this->quickPartyName = '';
        $this->quickPartyVat = '';
        $this->quickPartyPhone = '';
        $this->quickPartyCity = '';
        $this->quickPartyEmail = '';
    }

    protected function resetQuickProduct(): void
    {
        $this->quickProductLineIndex = null;
        $this->quickProductName = '';
        $this->quickProductSku = '';
        $this->quickProductPrice = '0';
        $this->quickProductTaxRate = '15';
        $this->quickProductTaxCategory = 'S';
        $this->quickProductUnitCode = 'PCE';
    }

    public function render()
    {
        $partyType = $this->direction === 'purchase' ? ['supplier', 'both'] : ['customer', 'both'];

        return view('livewire.invoicing.invoice-form', [
            'parties' => Party::query()
                ->whereIn('type', $partyType)
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name_ar')
                ->get(['id', 'name_ar', 'price', 'tax_category', 'tax_rate', 'unit_code']),
            'issuedInvoices' => $this->needsReference
                ? Invoice::query()
                    ->where('direction', $this->direction)
                    ->where('status', 'issued')
                    ->latest('id')
                    ->limit(50)
                    ->get(['id', 'invoice_number'])
                : collect(),
            'invoice' => $this->invoiceId
                ? Invoice::query()->find($this->invoiceId, ['id', 'status', 'invoice_number', 'direction'])
                : null,
        ])->title($this->direction === 'purchase' ? 'فاتورة مشتريات' : 'فاتورة مبيعات');
    }
}
