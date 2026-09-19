<div class="mx-auto max-w-5xl space-y-6" x-data="{ more: @js($this->needsReference || filled($note) || (float) $discount_amount > 0) }">
    <x-page-header
        :title="$invoiceId ? 'تعديل فاتورة' : ($direction === 'purchase' ? 'فاتورة مشتريات جديدة' : 'فاتورة مبيعات جديدة')"
        description="أضف العميل والبنود، ثم احفظ أو أصدر مباشرة."
    >
        <x-slot name="actions">
            <a href="{{ route($direction === 'purchase' ? 'purchases.index' : 'invoices.index') }}" wire:navigate>
                <x-secondary-button type="button">رجوع</x-secondary-button>
            </a>
        </x-slot>
    </x-page-header>

    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    {{-- البيانات الأساسية --}}
    <x-ui-card>
        <h2 class="mb-4 text-base font-semibold text-slate-900">بيانات الفاتورة</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <div class="flex items-center justify-between gap-2">
                    <x-input-label :value="$direction === 'purchase' ? 'المورد' : 'العميل'" />
                    <button
                        type="button"
                        wire:click="openQuickParty"
                        class="btn-ghost"
                    >
                        <x-icon name="plus" class="h-3.5 w-3.5" />
                        إضافة جديد
                    </button>
                </div>
                <select wire:model="party_id" class="ui-select mt-1 w-full">
                    <option value="">اختر...</option>
                    @foreach ($parties as $party)
                        <option value="{{ $party->id }}">{{ $party->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('party_id')" />
                @if ($parties->isEmpty())
                    <p class="mt-1 text-xs text-amber-700">
                        لا يوجد أطراف بعد. استخدم «إضافة جديد» أعلاه.
                    </p>
                @endif
            </div>

            <div>
                <x-input-label value="التاريخ" />
                <x-text-input type="date" wire:model="issue_date" class="mt-1 w-full" />
                <x-input-error :messages="$errors->get('issue_date')" />
            </div>

            <div>
                <x-input-label value="نوع الفاتورة" />
                <select wire:model="invoice_type" class="ui-select mt-1 w-full">
                    <option value="simplified">مبسطة</option>
                    <option value="standard">ضريبية</option>
                </select>
            </div>

            <div>
                <x-input-label value="نوع المستند" />
                <select wire:model.live="document_type_code" class="ui-select mt-1 w-full">
                    <option value="388">فاتورة</option>
                    <option value="381">إشعار دائن</option>
                    <option value="383">إشعار مدين</option>
                </select>
            </div>
        </div>

        @if ($this->needsReference)
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label value="الفاتورة المرجعية" />
                    <select wire:model="referenced_invoice_id" class="ui-select mt-1 w-full">
                        <option value="">اختر الفاتورة الأصلية...</option>
                        @foreach ($issuedInvoices as $ref)
                            <option value="{{ $ref->id }}">{{ $ref->invoice_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <div class="mt-4">
            <button
                type="button"
                class="btn-ghost"
                @click="more = ! more"
                x-text="more ? 'إخفاء الخيارات الإضافية' : 'خيارات إضافية (خصم / ملاحظات)'"
            ></button>

            <div x-show="more" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2" style="display: none;">
                <div>
                    <x-input-label value="خصم على الفاتورة" />
                    <x-text-input type="number" step="0.01" min="0" wire:model.live="discount_amount" class="mt-1 w-full" placeholder="0.00" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label value="ملاحظات" />
                    <textarea wire:model="note" class="ui-select mt-1 w-full" rows="2" placeholder="اختياري..."></textarea>
                </div>
            </div>
        </div>
    </x-ui-card>

    {{-- البنود --}}
    <x-ui-card :padding="false">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">بنود الفاتورة</h2>
                <p class="mt-0.5 text-sm text-slate-500">اختر منتجاً أو أضفه من هنا أو اكتب وصفاً يدوياً.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-secondary-button type="button" wire:click="openQuickProduct">
                    <x-icon name="plus" class="h-4 w-4" />
                    منتج جديد
                </x-secondary-button>
                <x-secondary-button type="button" wire:click="addLine">
                    <x-icon name="plus" class="h-4 w-4" />
                    بند جديد
                </x-secondary-button>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach ($lines as $index => $line)
                <div class="space-y-3 px-5 py-4" wire:key="line-{{ $index }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-semibold text-slate-500">بند {{ $index + 1 }}</span>
                        @if (count($lines) > 1)
                            <x-icon-button icon="delete" label="حذف البند" variant="delete" wire:click="removeLine({{ $index }})" />
                        @endif
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-4">
                            <div class="flex items-center justify-between gap-2">
                                <x-input-label value="منتج جاهز" />
                                <button
                                    type="button"
                                    wire:click="openQuickProduct({{ $index }})"
                                    class="btn-ghost"
                                >
                                    + منتج
                                </button>
                            </div>
                            <select
                                wire:model="lines.{{ $index }}.product_id"
                                wire:change="fillFromProduct({{ $index }})"
                                class="ui-select mt-1 w-full"
                            >
                                <option value="">كتابة يدوية</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name_ar }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-8">
                            <x-input-label value="الوصف" />
                            <x-text-input
                                wire:model="lines.{{ $index }}.description"
                                class="mt-1 w-full"
                                placeholder="اسم الخدمة أو المنتج"
                            />
                            <x-input-error :messages="$errors->get('lines.'.$index.'.description')" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-input-label value="الكمية" />
                            <x-text-input type="number" step="0.01" min="0" wire:model.live="lines.{{ $index }}.quantity" class="mt-1 w-full" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-input-label value="السعر" />
                            <x-text-input type="number" step="0.01" min="0" wire:model.live="lines.{{ $index }}.unit_price" class="mt-1 w-full" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-input-label value="الضريبة %" />
                            <x-text-input type="number" step="0.01" min="0" wire:model.live="lines.{{ $index }}.tax_rate" class="mt-1 w-full" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-input-label value="خصم البند" />
                            <x-text-input type="number" step="0.01" min="0" wire:model.live="lines.{{ $index }}.discount" class="mt-1 w-full" placeholder="0" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <x-input-error class="px-5 pb-2" :messages="$errors->get('lines')" />
    </x-ui-card>

    {{-- الملخص والإجراءات --}}
    <x-ui-card>
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-1 text-sm">
                <div class="flex min-w-[12rem] justify-between gap-6 text-slate-600">
                    <span>قبل الضريبة</span>
                    <span class="font-medium text-slate-900">{{ number_format($this->totals['lines_net'] - $this->totals['discount'], 2) }} ر.س</span>
                </div>
                <div class="flex min-w-[12rem] justify-between gap-6 text-slate-600">
                    <span>الضريبة</span>
                    <span class="font-medium text-slate-900">{{ number_format($this->totals['tax'], 2) }} ر.س</span>
                </div>
                <div class="flex min-w-[12rem] justify-between gap-6 border-t border-slate-100 pt-2 text-base font-bold text-slate-900">
                    <span>الإجمالي</span>
                    <span>{{ number_format($this->totals['total'], 2) }} ر.س</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-secondary-button type="button" wire:click="previewInvoice" wire:loading.attr="disabled" wire:target="previewInvoice">
                    <span wire:loading.remove wire:target="previewInvoice">معاينة الفاتورة</span>
                    <span wire:loading wire:target="previewInvoice">جاري التجهيز...</span>
                </x-secondary-button>
                <x-secondary-button type="button" wire:click="saveDraft" wire:loading.attr="disabled">
                    حفظ مسودة
                </x-secondary-button>
                <x-primary-button type="button" wire:click="issueInvoice" wire:loading.attr="disabled">
                    إصدار الفاتورة
                </x-primary-button>
            </div>
        </div>
    </x-ui-card>

    @if ($showQuickParty)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6" wire:key="quick-party-modal">
            <div class="absolute inset-0 bg-slate-900/40" wire:click="closeQuickParty"></div>
            <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-soft sm:p-6">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">
                            إضافة {{ $direction === 'purchase' ? 'مورد' : 'عميل' }} جديد
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">سيُضاف للقائمة ويُختار تلقائياً في الفاتورة.</p>
                    </div>
                    <button type="button" wire:click="closeQuickParty" class="rounded-lg p-1 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
                        <x-icon name="close" class="h-4 w-4" />
                    </button>
                </div>

                <form wire:submit="saveQuickParty" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label :value="$direction === 'purchase' ? 'اسم المورد' : 'اسم العميل'" />
                        <x-text-input wire:model="quickPartyName" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('quickPartyName')" />
                    </div>
                    <div>
                        <x-input-label value="الرقم الضريبي" />
                        <x-text-input wire:model="quickPartyVat" class="mt-1 w-full" maxlength="15" />
                        <x-input-error :messages="$errors->get('quickPartyVat')" />
                    </div>
                    <div>
                        <x-input-label value="الجوال" />
                        <x-text-input wire:model="quickPartyPhone" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('quickPartyPhone')" />
                    </div>
                    <div>
                        <x-input-label value="البريد الإلكتروني" />
                        <x-text-input type="email" wire:model="quickPartyEmail" class="mt-1 w-full" placeholder="لإرسال الفاتورة تلقائياً" />
                        <x-input-error :messages="$errors->get('quickPartyEmail')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label value="المدينة" />
                        <x-text-input wire:model="quickPartyCity" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('quickPartyCity')" />
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap justify-end gap-2">
                        <x-secondary-button type="button" wire:click="closeQuickParty">إلغاء</x-secondary-button>
                        <x-primary-button>حفظ واختيار</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showQuickProduct)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6" wire:key="quick-product-modal">
            <div class="absolute inset-0 bg-slate-900/40" wire:click="closeQuickProduct"></div>
            <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-soft sm:p-6">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">إضافة منتج جديد</h3>
                        <p class="mt-1 text-sm text-slate-500">سيُضاف للكتالوج ويُربط بالبند الحالي.</p>
                    </div>
                    <button type="button" wire:click="closeQuickProduct" class="rounded-lg p-1 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
                        <x-icon name="close" class="h-4 w-4" />
                    </button>
                </div>

                <form wire:submit="saveQuickProduct" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label value="اسم المنتج" />
                        <x-text-input wire:model="quickProductName" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('quickProductName')" />
                    </div>
                    <div>
                        <x-input-label value="رمز الصنف" />
                        <x-text-input wire:model="quickProductSku" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('quickProductSku')" />
                    </div>
                    <div>
                        <x-input-label value="السعر" />
                        <x-text-input type="number" step="0.01" min="0" wire:model="quickProductPrice" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('quickProductPrice')" />
                    </div>
                    <div>
                        <x-input-label value="فئة الضريبة" />
                        <select wire:model="quickProductTaxCategory" class="ui-select mt-1 w-full">
                            <option value="S">خاضع</option>
                            <option value="Z">صفرية</option>
                            <option value="E">معفى</option>
                            <option value="O">خارج النطاق</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="نسبة الضريبة %" />
                        <x-text-input type="number" step="0.01" min="0" wire:model="quickProductTaxRate" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('quickProductTaxRate')" />
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap justify-end gap-2">
                        <x-secondary-button type="button" wire:click="closeQuickProduct">إلغاء</x-secondary-button>
                        <x-primary-button>حفظ وربطه بالبند</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
