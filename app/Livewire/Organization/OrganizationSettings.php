<?php

namespace App\Livewire\Organization;

use App\Domains\Organization\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class OrganizationSettings extends Component
{
    public string $legal_name_ar = '';

    public string $legal_name_en = '';

    public string $vat_number = '';

    public string $cr_number = '';

    public string $building_number = '';

    public string $street = '';

    public string $district = '';

    public string $city = '';

    public string $postal_code = '';

    public string $additional_no = '';

    public string $phone = '';

    public string $email = '';

    public bool $is_vat_registered = true;

    public function mount(): void
    {
        $org = auth()->user()->tenant->organization;
        abort_unless($org, 404);

        $this->legal_name_ar = (string) ($org->legal_name_ar ?? '');
        $this->legal_name_en = (string) ($org->legal_name_en ?? '');
        $this->vat_number = (string) ($org->vat_number ?? '');
        $this->cr_number = (string) ($org->cr_number ?? '');
        $this->building_number = (string) ($org->building_number ?? '');
        $this->street = (string) ($org->street ?? '');
        $this->district = (string) ($org->district ?? '');
        $this->city = (string) ($org->city ?? '');
        $this->postal_code = (string) ($org->postal_code ?? '');
        $this->additional_no = (string) ($org->additional_no ?? '');
        $this->phone = (string) ($org->phone ?? '');
        $this->email = (string) ($org->email ?? '');
        $this->is_vat_registered = (bool) $org->is_vat_registered;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $validated = $this->validate([
            'legal_name_ar' => ['required', 'string', 'max:255'],
            'legal_name_en' => ['nullable', 'string', 'max:255'],
            'vat_number' => ['required', 'regex:/^3\d{13}3$/'],
            'cr_number' => ['required', 'string', 'max:20'],
            'building_number' => ['required', 'string', 'max:10'],
            'street' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'digits:5'],
            'additional_no' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_vat_registered' => ['boolean'],
        ], [
            'vat_number.regex' => 'الرقم الضريبي يجب أن يكون 15 رقماً ويبدأ وينتهي بـ 3.',
        ]);

        /** @var Organization $org */
        $org = auth()->user()->tenant->organization;
        $org->update($validated + [
            'seller_id_type' => 'CRN',
            'seller_id_value' => $validated['cr_number'],
        ]);

        auth()->user()->tenant->update([
            'name_ar' => $validated['legal_name_ar'],
            'name_en' => $validated['legal_name_en'] ?: $validated['legal_name_ar'],
        ]);

        session()->flash('status', 'تم حفظ بيانات المنشأة بنجاح.');
    }

    public function render()
    {
        return view('livewire.organization.organization-settings')
            ->title('بيانات المنشأة');
    }
}
