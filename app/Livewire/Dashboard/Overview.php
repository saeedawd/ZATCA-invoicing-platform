<?php

namespace App\Livewire\Dashboard;

use App\Domains\Dashboard\Services\DashboardStatsService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Overview extends Component
{
    public function render(DashboardStatsService $stats)
    {
        $user = auth()->user()->loadMissing(['tenant.organization']);
        $tenantId = (int) $user->tenant_id;
        $data = $stats->forTenant($tenantId);

        return view('livewire.dashboard.overview', [
            ...$data,
            'organization' => $user->tenant?->organization,
        ])->title('لوحة التحكم');
    }
}
