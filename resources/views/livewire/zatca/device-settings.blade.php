<div class="mx-auto max-w-5xl space-y-6">
    <x-page-header
        title="ربط جهاز الفوترة"
        description="اربط جهازك بهيئة الزكاة والضريبة والجمارك لإصدار الفواتير الإلكترونية المعتمدة."
    >
        <x-slot name="actions">
            <a href="{{ route('organization.edit') }}" wire:navigate>
                <x-secondary-button type="button">
                    <x-icon name="organization" class="h-4 w-4" />
                    بيانات المنشأة
                </x-secondary-button>
            </a>
            <a href="https://fatoora.zatca.gov.sa" target="_blank" rel="noopener noreferrer">
                <x-secondary-button type="button">
                    <x-icon name="external" class="h-4 w-4" />
                    بوابة فاتورة
                </x-secondary-button>
            </a>
        </x-slot>
    </x-page-header>

    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    @if ($errorMessage)
        <div class="alert-danger">{{ $errorMessage }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-ui-card>
            <div class="text-xs font-medium text-slate-500">حالة الربط</div>
            <div class="mt-2 flex items-center gap-2">
                @if ($activeDevice)
                    <x-status-badge status="onboarded" type="zatca" />
                    <span class="text-sm font-semibold text-slate-800">جهاز نشط</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/15">غير مربوط</span>
                @endif
            </div>
            <p class="mt-2 text-xs text-slate-500">
                {{ $activeDevice ? 'جاهز لإرسال الفواتير.' : 'اربط جهازاً لبدء الإرسال.' }}
            </p>
        </x-ui-card>

        <x-ui-card>
            <div class="text-xs font-medium text-slate-500">بيانات المنشأة</div>
            <div class="mt-2 text-sm font-semibold {{ $orgReady ? 'text-emerald-700' : 'text-amber-800' }}">
                {{ $orgReady ? 'مكتملة' : 'ناقصة' }}
            </div>
            <p class="mt-2 truncate text-xs text-slate-500" dir="ltr" title="{{ $organization?->vat_number }}">
                الرقم الضريبي: {{ $organization?->vat_number ?: '—' }}
            </p>
        </x-ui-card>

        <x-ui-card>
            <div class="text-xs font-medium text-slate-500">الجهة</div>
            <div class="mt-2 text-sm font-semibold text-slate-800">هيئة الزكاة والضريبة والجمارك</div>
            <p class="mt-2 text-xs text-slate-500">
                {{ $solutionName }} · v{{ $solutionVersion }}
            </p>
        </x-ui-card>
    </div>

    @if (! $orgReady)
        <div class="alert-warning">
            أكمل
            <a href="{{ route('organization.edit') }}" class="font-semibold underline underline-offset-2" wire:navigate>بيانات المنشأة</a>
            أولاً (اسم الشركة، الرقم الضريبي، السجل التجاري، والعنوان) قبل طلب الربط.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <x-ui-card class="lg:col-span-2">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <x-icon name="zatca" class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">كيف تحصل على الرمز؟</h2>
                    <p class="mt-1 text-sm text-slate-500">ثلاث خطوات فقط.</p>
                </div>
            </div>

            <ol class="mt-5 space-y-3">
                <li class="flex gap-3 text-sm">
                    <span @class([
                        'mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                        'bg-emerald-100 text-emerald-700' => $orgReady,
                        'bg-slate-100 text-slate-600' => ! $orgReady,
                    ])>
                        @if ($orgReady)
                            <x-icon name="check" class="h-3.5 w-3.5" />
                        @else
                            1
                        @endif
                    </span>
                    <span class="text-slate-700">أكمل بيانات المنشأة والرقم الضريبي هنا في المنصة.</span>
                </li>
                <li class="flex gap-3 text-sm">
                    <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">2</span>
                    <span class="text-slate-700">
                        ادخل
                        <a href="https://fatoora.zatca.gov.sa" target="_blank" rel="noopener noreferrer" class="font-semibold text-brand-700 underline underline-offset-2">بوابة فاتورة</a>
                        وأنشئ رمز تحقق (OTP) للمنشأة.
                    </span>
                </li>
                <li class="flex gap-3 text-sm">
                    <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">3</span>
                    <span class="text-slate-700">الصق الرمز في النموذج وأتمم الربط قبل انتهاء صلاحيته.</span>
                </li>
            </ol>

            <div class="mt-5 rounded-xl bg-slate-50 px-3.5 py-3 text-xs leading-relaxed text-slate-600">
                تأكد أن الرقم الضريبي في المنصة مطابق لحسابك في بوابة فاتورة. الجهاز والشهادة يخصّان منشأتك فقط.
            </div>
        </x-ui-card>

        <x-ui-card class="lg:col-span-3">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                    <x-icon name="lock" class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">إدخال رمز التحقق</h2>
                    <p class="mt-1 text-sm text-slate-500">الرمز من 6 أرقام من بوابة فاتورة.</p>
                </div>
            </div>

            <form wire:submit="onboard" class="mt-5 space-y-4">
                <div>
                    <x-input-label value="رمز التحقق (OTP)" />
                    <x-text-input
                        wire:model="otp"
                        class="mt-1.5 w-full max-w-xs tracking-[0.35em]"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        placeholder="••••••"
                        autocomplete="one-time-code"
                        dir="ltr"
                    />
                    <x-input-error class="mt-2" :messages="$errors->get('otp')" />
                    <p class="mt-1.5 text-xs text-slate-500">
                        أنشئ الرمز من بوابة فاتورة ثم أدخله هنا مباشرة.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <x-primary-button :disabled="! $orgReady" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="onboard">ربط الجهاز</span>
                        <span wire:loading wire:target="onboard">جاري الربط...</span>
                    </x-primary-button>
                    @unless ($orgReady)
                        <span class="text-xs text-amber-800">أكمل بيانات المنشأة أولاً لتفعيل الزر.</span>
                    @endunless
                </div>
            </form>
        </x-ui-card>
    </div>

    <x-ui-card :padding="false">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-semibold text-slate-900">الأجهزة المرتبطة</h2>
                <p class="mt-0.5 text-sm text-slate-500">سجل أجهزة الفوترة لهذا الحساب.</p>
            </div>
            <span class="text-sm font-medium text-slate-500">{{ $devices->count() }} جهاز</span>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الرقم التسلسلي</th>
                        <th>الحالة</th>
                        <th>العداد</th>
                        <th>تاريخ الربط</th>
                        <th class="col-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($devices as $device)
                        <tr>
                            <td class="max-w-[18rem]">
                                <div class="truncate font-medium text-slate-900" title="{{ $device->device_serial }}">
                                    {{ $device->device_serial }}
                                </div>
                                <div class="mt-0.5 text-xs text-slate-400">
                                    {{ $device->solution_name }} · v{{ $device->version }}
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge :status="$device->status" type="zatca" />
                                    @if ($activeDevice?->id === $device->id)
                                        <span class="text-[11px] font-semibold text-brand-700">نشط</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ number_format($device->invoice_counter) }}</td>
                            <td class="text-slate-500" dir="ltr">
                                {{ optional($device->otp_used_at)->timezone(config('app.timezone'))->format('Y-m-d H:i') ?: '—' }}
                            </td>
                            <td class="col-actions">
                                <div x-data="{ copied: false }" class="flex items-center justify-end">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                                        title="نسخ الرقم التسلسلي"
                                        @click="
                                            navigator.clipboard.writeText(@js($device->device_serial));
                                            copied = true;
                                            setTimeout(() => copied = false, 1500);
                                        "
                                    >
                                        <x-icon x-show="!copied" name="copy" class="h-4 w-4" />
                                        <x-icon x-cloak x-show="copied" name="check" class="h-4 w-4 text-emerald-600" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="data-table-empty">لا توجد أجهزة بعد. أدخل رمز التحقق لربط أول جهاز.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui-card>
</div>
