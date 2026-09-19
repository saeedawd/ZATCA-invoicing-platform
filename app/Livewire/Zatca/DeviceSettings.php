<?php

namespace App\Livewire\Zatca;

use App\Domains\Zatca\Models\ZatcaDevice;
use App\Domains\Zatca\Services\ZatcaOnboardingService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;
use Throwable;

#[Layout('layouts.app')]
class DeviceSettings extends Component
{
    public string $otp = '';

    public string $environment = 'production';

    public ?string $errorMessage = null;

    public function onboard(ZatcaOnboardingService $service): void
    {
        abort_unless(auth()->user()->canManage(), 403);

        $this->errorMessage = null;

        $this->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'أدخل رمز التحقق.',
            'otp.digits' => 'رمز التحقق يجب أن يكون 6 أرقام.',
        ]);

        $organization = auth()->user()->loadMissing('tenant.organization')->tenant?->organization;
        abort_unless($organization, 404);

        try {
            $service->createDevice($organization, $this->otp, $this->environment ?: 'production');
            $this->reset('otp');

            session()->flash(
                'status',
                'تم ربط الجهاز بنجاح مع هيئة الزكاة والضريبة والجمارك. الشهادة خاصة بمنشأة هذا الحساب فقط.'
            );
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (Throwable $e) {
            report($e);
            $this->errorMessage = 'تعذر إكمال الربط: '.$e->getMessage();
        }
    }

    public function render()
    {
        $user = auth()->user()->loadMissing('tenant.organization');
        $organization = $user->tenant?->organization;
        $devices = ZatcaDevice::query()
            ->latest('id')
            ->get([
                'id',
                'device_serial',
                'environment',
                'status',
                'invoice_counter',
                'otp_used_at',
                'solution_name',
                'version',
                'created_at',
            ]);

        $activeDevice = $devices->firstWhere('status', 'onboarded');

        return view('livewire.zatca.device-settings', [
            'devices' => $devices,
            'activeDevice' => $activeDevice,
            'organization' => $organization,
            'orgReady' => (bool) $organization?->isProfileComplete(),
            'solutionName' => (string) config('zatca.solution_name'),
            'solutionVersion' => (string) config('zatca.solution_version'),
        ])->title('الزكاة والضريبة');
    }
}
