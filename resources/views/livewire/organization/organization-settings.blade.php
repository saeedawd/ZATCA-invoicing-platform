<div class="mx-auto max-w-4xl space-y-6">
    <x-page-header
        title="بيانات المنشأة"
        description="بيانات شركتك أو مؤسستك المستخدمة في الفوترة الإلكترونية."
    />

    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    <x-ui-card>
        <form wire:submit="save" class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <x-input-label value="اسم الشركة / المؤسسة (عربي)" />
                <x-text-input wire:model="legal_name_ar" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->get('legal_name_ar')" />
            </div>
            <div>
                <x-input-label value="اسم الشركة / المؤسسة (إنجليزي)" />
                <x-text-input wire:model="legal_name_en" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label value="الرقم الضريبي" />
                <x-text-input wire:model="vat_number" class="mt-1 block w-full" maxlength="15" />
                <x-input-error :messages="$errors->get('vat_number')" />
            </div>
            <div>
                <x-input-label value="السجل التجاري" />
                <x-text-input wire:model="cr_number" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->get('cr_number')" />
            </div>
            <div>
                <x-input-label value="رقم المبنى" />
                <x-text-input wire:model="building_number" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label value="الرمز البريدي" />
                <x-text-input wire:model="postal_code" class="mt-1 block w-full" />
            </div>
            <div class="md:col-span-2">
                <x-input-label value="الشارع" />
                <x-text-input wire:model="street" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label value="الحي" />
                <x-text-input wire:model="district" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label value="المدينة" />
                <x-text-input wire:model="city" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label value="الجوال" />
                <x-text-input wire:model="phone" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label value="البريد" />
                <x-text-input wire:model="email" class="mt-1 block w-full" />
            </div>
            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="is_vat_registered" class="rounded border-slate-300 text-brand-700 focus:ring-brand-600" />
                    <span>منشأة مسجلة في ضريبة القيمة المضافة</span>
                </label>
            </div>
            <div class="md:col-span-2">
                <x-primary-button>حفظ</x-primary-button>
            </div>
        </form>
    </x-ui-card>
</div>
