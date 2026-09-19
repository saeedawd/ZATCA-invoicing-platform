<div>
    @if ($sent)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-6 text-center" role="status">
            <p class="text-base font-semibold text-emerald-900">وصلتنا رسالتك بنجاح</p>
            <p class="mt-2 text-sm leading-7 text-emerald-800">
                بنراجعها ونرد عليك قريبًا على بريدك. لو حابب تراسلنا مباشرة:
                <a href="mailto:{{ config('seo.contact_to', 'info@zatca.app') }}" class="font-semibold underline decoration-emerald-300 underline-offset-2 hover:text-emerald-950">
                    {{ config('seo.contact_to', 'info@zatca.app') }}
                </a>
            </p>
        </div>
    @else
        <form wire:submit="send" class="space-y-5">
            <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                <label for="website">الموقع</label>
                <input id="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="name" value="الاسم" />
                    <x-text-input
                        wire:model="name"
                        id="name"
                        class="mt-1.5 block w-full"
                        type="text"
                        required
                        autocomplete="name"
                        placeholder="اسمك أو اسم المنشأة"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" value="بريدك الإلكتروني" />
                    <x-text-input
                        wire:model="email"
                        id="email"
                        class="mt-1.5 block w-full"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="name@example.com"
                        dir="ltr"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="message" value="الرسالة" />
                <textarea
                    wire:model="message"
                    id="message"
                    rows="7"
                    required
                    placeholder="اكتب استفسارك بوضوح… مثال: كيف أربط جهازي مع زاتكا؟"
                    class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-brand-600 focus:ring-brand-600"
                ></textarea>
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            </div>

            <div class="flex flex-col gap-3 pt-1 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs leading-5 text-slate-500 order-2 sm:order-1">
                    بالإرسال، رسالتك توصل إلى
                    <span class="font-medium text-slate-700" dir="ltr">{{ config('seo.contact_to', 'info@zatca.app') }}</span>
                </p>
                <x-primary-button class="order-1 w-full justify-center px-6 py-3 sm:order-2 sm:w-auto" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="send">إرسال الرسالة</span>
                    <span wire:loading wire:target="send">جارٍ الإرسال…</span>
                </x-primary-button>
            </div>
        </form>
    @endif
</div>
