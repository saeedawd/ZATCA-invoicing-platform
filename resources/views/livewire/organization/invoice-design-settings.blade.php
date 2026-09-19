<div class="mx-auto max-w-5xl space-y-6">
    <x-page-header
        title="تصميم الفاتورة"
        description="اختر قالباً جاهزاً، أضف شعار شركتك، وخصص الألوان قبل إصدار الفواتير."
    />

    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <x-ui-card>
            <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">معرض القوالب</h2>
                    <p class="mt-1 text-sm text-slate-500">اضغط على القالب ليُطبَّق مباشرة على فواتيرك.</p>
                </div>
                <div class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-800">
                    القالب الحالي:
                    {{ $templates[$invoice_template]['name_ar'] ?? $invoice_template }}
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($templates as $template)
                    <button
                        type="button"
                        wire:key="template-card-{{ $template['key'] }}"
                        wire:click="selectTemplate('{{ $template['key'] }}')"
                        wire:loading.attr="disabled"
                        class="rounded-2xl border p-3 text-right transition disabled:opacity-60 {{ $invoice_template === $template['key'] ? 'border-brand-600 ring-2 ring-brand-200 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300 bg-white' }}"
                    >
                        <div class="mb-3 overflow-hidden rounded-xl border border-slate-100 bg-white">
                            @if ($template['key'] === 'classic')
                                <div class="h-14 px-3 py-2 text-[10px] font-bold text-white" style="background: {{ $template['preview_primary'] }};">ترويسة ملونة</div>
                                <div class="h-1.5" style="background: {{ $template['preview_secondary'] }};"></div>
                                <div class="space-y-1.5 p-3">
                                    <div class="h-2 w-2/3 rounded bg-slate-200"></div>
                                    <div class="h-8 rounded" style="background: {{ $template['preview_primary'] }}18;"></div>
                                </div>
                            @elseif ($template['key'] === 'minimal')
                                <div class="h-2" style="background: {{ $template['preview_secondary'] }};"></div>
                                <div class="space-y-2 p-3">
                                    <div class="h-2 w-1/2 rounded" style="background: {{ $template['preview_primary'] }};"></div>
                                    <div class="h-2 w-2/3 rounded bg-slate-200"></div>
                                    <div class="border-y border-slate-200 py-2">
                                        <div class="h-2 w-full rounded bg-slate-100"></div>
                                    </div>
                                </div>
                            @elseif ($template['key'] === 'modern')
                                <div class="flex">
                                    <div class="w-2 self-stretch" style="background: {{ $template['preview_primary'] }};"></div>
                                    <div class="flex-1">
                                        <div class="h-12 px-2 py-2 text-[10px] font-bold text-white" style="background: {{ $template['preview_primary'] }};">شريط جانبي</div>
                                        <div class="h-1" style="background: {{ $template['preview_secondary'] }};"></div>
                                        <div class="space-y-1.5 p-3">
                                            <div class="h-2 w-2/3 rounded bg-slate-200"></div>
                                            <div class="h-8 rounded bg-slate-50"></div>
                                        </div>
                                    </div>
                                </div>
                            @elseif ($template['key'] === 'elegant')
                                <div class="m-2 rounded-lg border border-amber-200 bg-amber-50/60 p-3">
                                    <div class="h-2 w-1/2 rounded" style="background: {{ $template['preview_primary'] }};"></div>
                                    <div class="mt-2 h-1 rounded" style="background: {{ $template['preview_secondary'] }};"></div>
                                </div>
                                <div class="space-y-1.5 px-3 pb-3">
                                    <div class="h-2 w-2/3 rounded bg-slate-200"></div>
                                    <div class="h-8 rounded border border-amber-100 bg-amber-50/40"></div>
                                </div>
                            @else
                                <div class="h-16 px-3 py-3 text-[10px] font-bold text-white" style="background: {{ $template['preview_primary'] }};">هيدر قوي</div>
                                <div class="h-2" style="background: {{ $template['preview_secondary'] }};"></div>
                                <div class="space-y-1.5 p-3">
                                    <div class="h-2 w-2/3 rounded bg-slate-200"></div>
                                    <div class="mx-auto h-10 w-10 rounded-full border-2" style="border-color: {{ $template['preview_primary'] }};"></div>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $template['name_ar'] }}</div>
                                <div class="mt-0.5 text-xs text-slate-500">{{ $template['description'] }}</div>
                            </div>
                            @if ($invoice_template === $template['key'])
                                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white">
                                    <x-icon name="check" class="h-3.5 w-3.5" />
                                </span>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('invoice_template')" />
        </x-ui-card>

        <x-ui-card>
            <div class="mb-4">
                <h2 class="text-base font-semibold text-slate-900">الشعار والألوان</h2>
                <p class="mt-1 text-sm text-slate-500">يمكنك إظهار الشعار أو إخفاؤه دون حذف الملف. بعد تعديل الألوان اضغط حفظ.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="space-y-3">
                    <x-input-label value="شعار الشركة" />
                    <div class="flex items-center gap-4">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" alt="شعار جديد" class="h-16 w-16 rounded-xl border border-slate-200 object-contain bg-white p-1">
                        @elseif ($existingLogoUrl)
                            <img src="{{ $existingLogoUrl }}" alt="شعار الشركة" class="h-16 w-16 rounded-xl border border-slate-200 object-contain bg-white p-1">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 text-[11px] text-slate-400">
                                بدون شعار
                            </div>
                        @endif
                        <div class="space-y-2">
                            <input
                                type="file"
                                wire:model="logo"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="block w-full text-sm text-slate-600 file:me-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
                            />
                            @if ($existingLogoUrl || $logo)
                                <button type="button" wire:click="removeLogo" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                                    حذف الشعار
                                </button>
                            @endif
                        </div>
                    </div>
                    <div wire:loading wire:target="logo" class="text-xs text-slate-500">جاري رفع الشعار...</div>
                    <x-input-error :messages="$errors->get('logo')" />

                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" wire:model="show_logo" class="rounded border-slate-300 text-brand-700 focus:ring-brand-600" />
                        <span>إظهار الشعار على الفاتورة</span>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="اللون الأساسي" />
                        <div class="mt-1 flex items-center gap-2">
                            <input type="color" wire:model.live="brand_primary" class="h-10 w-12 cursor-pointer rounded border border-slate-200 bg-white p-1" />
                            <x-text-input wire:model.live="brand_primary" class="block w-full" maxlength="7" />
                        </div>
                        <x-input-error :messages="$errors->get('brand_primary')" />
                    </div>
                    <div>
                        <x-input-label value="اللون الثانوي" />
                        <div class="mt-1 flex items-center gap-2">
                            <input type="color" wire:model.live="brand_secondary" class="h-10 w-12 cursor-pointer rounded border border-slate-200 bg-white p-1" />
                            <x-text-input wire:model.live="brand_secondary" class="block w-full" maxlength="7" />
                        </div>
                        <x-input-error :messages="$errors->get('brand_secondary')" />
                    </div>
                    <div class="col-span-2">
                        <x-input-label value="نص التذييل (اختياري)" />
                        <textarea
                            wire:model="invoice_footer"
                            rows="3"
                            class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            placeholder="مثال: شكراً لتعاملكم معنا — أو بيانات التحويل البنكي"
                        ></textarea>
                        <x-input-error :messages="$errors->get('invoice_footer')" />
                    </div>
                </div>
            </div>
        </x-ui-card>

        <div class="flex flex-wrap items-center gap-3">
            <x-primary-button>حفظ التصميم</x-primary-button>
            <x-secondary-button type="button" wire:click="preview" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="preview">معاينة PDF</span>
                <span wire:loading wire:target="preview">جاري تجهيز المعاينة...</span>
            </x-secondary-button>
        </div>
    </form>
</div>
