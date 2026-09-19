<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header
        title="العملاء والموردون"
        description="إدارة بيانات الأطراف المستخدمة في الفواتير."
    >
        <x-slot name="actions">
            <x-primary-button type="button" wire:click="create">
                <x-icon name="plus" class="h-4 w-4" />
                إضافة طرف
            </x-primary-button>
        </x-slot>
    </x-page-header>

    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    @if ($showForm)
        <x-ui-card>
            <form wire:submit="save" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <x-input-label value="النوع" />
                    <select wire:model="type" class="ui-select mt-1 w-full">
                        <option value="customer">عميل</option>
                        <option value="supplier">مورد</option>
                        <option value="both">عميل ومورد</option>
                    </select>
                </div>
                <div>
                    <x-input-label value="الاسم" />
                    <x-text-input wire:model="name" class="mt-1 w-full" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label value="الرقم الضريبي" />
                    <x-text-input wire:model="vat_number" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="السجل التجاري" />
                    <x-text-input wire:model="cr_number" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="المدينة" />
                    <x-text-input wire:model="city" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="الجوال" />
                    <x-text-input wire:model="phone" class="mt-1 w-full" />
                </div>
                <div class="md:col-span-3">
                    <x-primary-button>حفظ</x-primary-button>
                </div>
            </form>
        </x-ui-card>
    @endif

    <x-data-table search search-placeholder="بحث بالاسم...">
        <x-slot name="filters">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">النوع</label>
                <select wire:model.live="typeFilter" class="ui-select">
                    <option value="">كل الأنواع</option>
                    <option value="customer">عميل</option>
                    <option value="supplier">مورد</option>
                    <option value="both">عميل ومورد</option>
                </select>
            </div>
        </x-slot>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>النوع</th>
                    <th>الرقم الضريبي</th>
                    <th>المدينة</th>
                    <th class="col-actions">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($parties as $party)
                    <tr wire:key="party-{{ $party->id }}">
                        <td class="font-medium text-slate-900">{{ $party->name }}</td>
                        <td><x-enum-label type="partyType" :value="$party->type" /></td>
                        <td>{{ $party->vat_number }}</td>
                        <td>{{ $party->city }}</td>
                        <td class="col-actions">
                            <div>
                                <x-icon-button
                                    icon="view"
                                    label="كشف حساب"
                                    variant="view"
                                    :href="route('parties.statement', $party)"
                                    wire:navigate
                                />
                                <x-icon-button icon="edit" label="تعديل" variant="edit" wire:click="edit({{ $party->id }})" />
                                <x-icon-button icon="delete" label="حذف" variant="delete" wire:click="delete({{ $party->id }})" wire:confirm="حذف الطرف؟" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="data-table-empty">لا توجد أطراف مطابقة.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-slot name="footer">
            {{ $parties->links() }}
        </x-slot>
    </x-data-table>
</div>
