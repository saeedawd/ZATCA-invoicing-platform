<?php

namespace App\Livewire\Invoicing;

use App\Domains\Catalog\Models\Party;
use App\Domains\Catalog\Models\Product;
use App\Domains\Dashboard\Services\DashboardStatsService;
use App\Domains\Invoicing\Models\Quote;
use App\Domains\Invoicing\Services\QuotePdfRenderer;
use App\Domains\Invoicing\Services\QuoteService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class QuoteForm extends Component
{
    public ?int $quoteId = null;

    public ?int $party_id = null;

    public string $issue_date = '';

    public string $valid_until = '';

    public string $note = '';

    public string $discount_amount = '0';

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

    public function mount(mixed $quote = null): void
    {
        if ($quote !== null && ! $quote instanceof Quote) {
            $quote = Quote::query()->findOrFail($quote);
        }

        $this->issue_date = now()->toDateString();
        $this->valid_until = now()->addDays(14)->toDateString();

        if ($quote) {
            abort_unless($quote->isEditable(), 403);
            $quote->loadMissing('lines');
            $this->quoteId = $quote->id;
            $this->party_id = $quote->party_id;
            $this->issue_date = optional($quote->issue_date)->format('Y-m-d') ?: now()->toDateString();
            $this->valid_until = optional($quote->valid_until)->format('Y-m-d') ?: '';
            $this->note = (string) $quote->note;
            $this->discount_amount = (string) $quote->discount_amount;
            $this->lines = $quote->lines->map(fn ($line) => [
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => (string) $line->quantity,
                'unit_price' => (string) $line->unit_price,
                'discount' => (string) $line->discount,
                'tax_category' => $line->tax_category,
                'tax_rate' => (string) $line->tax_rate,
                'unit_code' => $line->unit_code,
            ])->all();
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
        ]);

        $party = Party::create([
            'tenant_id' => auth()->user()->tenant_id,
            'type' => 'customer',
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
        session()->flash('status', 'تم إضافة العميل واختياره في عرض السعر.');
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

    public function saveDraft(QuoteService $service)
    {
        return $this->persist($service, false);
    }

    public function sendQuote(QuoteService $service)
    {
        return $this->persist($service, true);
    }

    public function previewQuote(QuotePdfRenderer $renderer): StreamedResponse
    {
        abort_unless(auth()->user()->canManage(), 403);

        $this->validateForm();

        $computed = app(\App\Domains\Invoicing\Services\InvoiceCalculator::class)
            ->calculate($this->lines, (float) $this->discount_amount);

        $quote = new Quote([
            'tenant_id' => auth()->user()->tenant_id,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'quote_number' => 'معاينة',
            'party_id' => $this->party_id,
            'issue_date' => $this->issue_date,
            'valid_until' => $this->valid_until ?: null,
            'note' => $this->note !== '' ? $this->note : 'معاينة عرض سعر — لم يتم الإرسال بعد.',
            'line_extension_amount' => $computed['totals']['line_extension_amount'],
            'discount_amount' => $computed['totals']['discount_amount'],
            'taxable_amount' => $computed['totals']['taxable_amount'],
            'tax_amount' => $computed['totals']['tax_amount'],
            'total_amount' => $computed['totals']['total_amount'],
            'status' => 'draft',
        ]);

        $quote->setRelation('party', $this->party_id ? Party::find($this->party_id) : null);
        $quote->setRelation('lines', Collection::make($computed['lines'])->map(fn ($line) => new \App\Domains\Invoicing\Models\QuoteLine($line)));
        $quote->setRelation('tenant', auth()->user()->tenant);

        $binary = $renderer->previewDraftBinary($quote);

        return response()->streamDownload(
            static function () use ($binary): void {
                echo $binary;
            },
            'quote-preview-'.now()->format('Ymd-His').'.pdf',
            ['Content-Type' => 'application/pdf', 'Cache-Control' => 'no-store, no-cache, must-revalidate']
        );
    }

    protected function persist(QuoteService $service, bool $send)
    {
        abort_unless(auth()->user()->canManage(), 403);
        $this->validateForm();

        $payload = [
            'party_id' => $this->party_id,
            'issue_date' => $this->issue_date,
            'valid_until' => $this->valid_until ?: null,
            'note' => $this->note,
            'discount_amount' => $this->discount_amount,
        ];

        if ($this->quoteId) {
            $quote = Quote::findOrFail($this->quoteId);
            $quote = $service->updateDraft($quote, auth()->user(), $payload, $this->lines);
        } else {
            $quote = $service->createDraft(auth()->user(), $payload, $this->lines);
            $this->quoteId = $quote->id;
        }

        if ($send) {
            $service->send($quote, auth()->user());
            session()->flash('status', 'تم إرسال عرض السعر.');

            return $this->redirect(route('quotes.show', $quote), navigate: true);
        }

        session()->flash('status', 'تم حفظ مسودة عرض السعر.');

        return $this->redirect(route('quotes.edit', $quote), navigate: true);
    }

    protected function validateForm(): void
    {
        $this->validate([
            'party_id' => ['nullable', 'exists:parties,id'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'lines.*.description.required' => 'وصف البند مطلوب.',
            'lines.*.quantity.gt' => 'كمية البند يجب أن تكون أكبر من صفر.',
        ]);
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
        return view('livewire.invoicing.quote-form', [
            'parties' => Party::query()
                ->whereIn('type', ['customer', 'both'])
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name_ar')
                ->get(['id', 'name_ar', 'price', 'tax_category', 'tax_rate', 'unit_code']),
            'quote' => $this->quoteId
                ? Quote::query()->find($this->quoteId, ['id', 'status', 'quote_number'])
                : null,
        ])->title($this->quoteId ? 'تعديل عرض سعر' : 'عرض سعر جديد');
    }
}
