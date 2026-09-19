<?php

namespace App\Livewire\Organization;

use App\Domains\Invoicing\Services\InvoicePdfRenderer;
use App\Domains\Invoicing\Support\InvoiceTemplateCatalog;
use App\Domains\Organization\Models\Organization;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class InvoiceDesignSettings extends Component
{
    use WithFileUploads;

    public string $invoice_template = InvoiceTemplateCatalog::DEFAULT;

    public bool $show_logo = true;

    public string $brand_primary = '#0b5c41';

    public string $brand_secondary = '#c9a24b';

    public string $invoice_footer = '';

    public $logo = null;

    public ?string $existingLogoUrl = null;

    public function mount(): void
    {
        $org = auth()->user()->tenant->organization;
        abort_unless($org, 404);

        $this->invoice_template = InvoiceTemplateCatalog::resolve((string) ($org->invoice_template ?: InvoiceTemplateCatalog::DEFAULT));
        $this->show_logo = (bool) $org->show_logo;
        $this->brand_primary = (string) ($org->brand_primary ?: '#0b5c41');
        $this->brand_secondary = (string) ($org->brand_secondary ?: '#c9a24b');
        $this->invoice_footer = (string) ($org->invoice_footer ?? '');
        $this->existingLogoUrl = $this->logoUrl($org);
    }

    public function selectTemplate(string $key): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        if (! InvoiceTemplateCatalog::isValid($key)) {
            return;
        }

        $template = InvoiceTemplateCatalog::all()[$key];

        $this->invoice_template = $key;
        $this->brand_primary = $template['preview_primary'];
        $this->brand_secondary = $template['preview_secondary'];

        /** @var Organization $org */
        $org = auth()->user()->tenant->organization;
        $org->update([
            'invoice_template' => $this->invoice_template,
            'brand_primary' => $this->brand_primary,
            'brand_secondary' => $this->brand_secondary,
        ]);

        session()->flash('status', 'تم تطبيق قالب «'.$template['name_ar'].'» بنجاح.');
    }

    public function removeLogo(): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        if ($this->logo) {
            $this->logo = null;

            return;
        }

        /** @var Organization $org */
        $org = auth()->user()->tenant->organization;

        if ($org->logo_path && Storage::disk('public')->exists($org->logo_path)) {
            Storage::disk('public')->delete($org->logo_path);
        }

        $org->update(['logo_path' => null]);
        $this->existingLogoUrl = null;

        session()->flash('status', 'تم حذف شعار الشركة.');
    }

    public function save(): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $validated = $this->validate([
            'invoice_template' => ['required', 'in:'.implode(',', InvoiceTemplateCatalog::keys())],
            'show_logo' => ['boolean'],
            'brand_primary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_secondary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'invoice_footer' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'brand_primary.regex' => 'اللون الأساسي يجب أن يكون بصيغة Hex مثل #0b5c41.',
            'brand_secondary.regex' => 'اللون الثانوي يجب أن يكون بصيغة Hex مثل #c9a24b.',
            'logo.max' => 'حجم الشعار يجب ألا يتجاوز 2 ميجابايت.',
        ]);

        /** @var Organization $org */
        $org = auth()->user()->tenant->organization;

        if ($this->logo) {
            if ($org->logo_path && Storage::disk('public')->exists($org->logo_path)) {
                Storage::disk('public')->delete($org->logo_path);
            }

            $extension = $this->logo->getClientOriginalExtension() ?: 'png';
            $path = $this->logo->storeAs(
                sprintf('tenants/%d/branding', $org->tenant_id),
                'logo.'.$extension,
                'public'
            );
            $validated['logo_path'] = $path;
        }

        unset($validated['logo']);

        $org->update($validated);

        $this->logo = null;
        $this->existingLogoUrl = $this->logoUrl($org->fresh());

        session()->flash('status', 'تم حفظ تصميم الفاتورة بنجاح.');
    }

    public function preview(InvoicePdfRenderer $renderer): StreamedResponse
    {
        abort_unless(auth()->user()->canManage(), 403);

        $this->validate([
            'invoice_template' => ['required', 'in:'.implode(',', InvoiceTemplateCatalog::keys())],
            'show_logo' => ['boolean'],
            'brand_primary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_secondary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'invoice_footer' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        /** @var Organization $org */
        $org = auth()->user()->tenant->organization;

        $overrides = [
            'invoice_template' => $this->invoice_template,
            'show_logo' => $this->show_logo,
            'brand_primary' => $this->brand_primary,
            'brand_secondary' => $this->brand_secondary,
            'invoice_footer' => $this->invoice_footer !== '' ? $this->invoice_footer : null,
            'logo_path' => $org->logo_path,
        ];

        if ($this->logo) {
            $tempPath = $this->logo->store(
                sprintf('tenants/%d/branding/tmp', $org->tenant_id),
                'public'
            );
            $overrides['logo_path'] = $tempPath;
        }

        $binary = $renderer->previewBinary($org, $overrides);

        if ($this->logo && isset($tempPath)) {
            Storage::disk('public')->delete($tempPath);
        }

        $filename = sprintf(
            'invoice-preview-%s-%s.pdf',
            $this->invoice_template,
            now()->format('His')
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

    public function render()
    {
        return view('livewire.organization.invoice-design-settings', [
            'templates' => InvoiceTemplateCatalog::all(),
        ])->title('تصميم الفاتورة');
    }

    protected function logoUrl(?Organization $org): ?string
    {
        if (! $org?->logo_path || ! Storage::disk('public')->exists($org->logo_path)) {
            return null;
        }

        return Storage::disk('public')->url($org->logo_path);
    }
}
