<?php

namespace App\Livewire\Profile;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class ManageProfile extends Component
{
    #[On('profile-updated')]
    #[On('password-updated')]
    public function refreshSummary(): void
    {
        // إعادة رسم الملخص بعد تحديث البيانات
    }

    public function render()
    {
        $user = auth()->user()->loadMissing('tenant');

        $initials = collect(preg_split('/\s+/u', trim((string) $user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->implode('');

        return view('livewire.profile.manage-profile', [
            'user' => $user,
            'tenant' => $user->tenant,
            'initials' => $initials ?: 'م',
        ])->title('الملف الشخصي');
    }
}
