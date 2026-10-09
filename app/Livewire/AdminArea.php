<?php

namespace App\Livewire;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Position;
use App\Models\SchoolClass;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Ringkasan khusus admin (pengganti placeholder panel.area).
 * Route sudah diproteksi middleware role:admin.
 */
class AdminArea extends Component
{
    #[Layout('components.layouts.panel')]
    public function render()
    {
        $activeUsers = User::query()->where('status', UserStatus::Active->value)->count();
        $totalUsers = User::query()->count();

        return view('livewire.admin-area', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $totalUsers - $activeUsers,
            'newestUsers' => User::query()
                ->orderByDesc('id')
                ->limit(5)
                ->get(['id', 'name', 'email', 'role', 'status']),
            'latestLogs' => AuditLog::query()
                ->with('user:id,name')
                ->orderByDesc('id')
                ->limit(8)
                ->get(['id', 'user_id', 'action', 'model_type', 'created_at']),
            'masterCounts' => [
                'Angkatan' => Batch::query()->count(),
                'Kelas' => SchoolClass::query()->count(),
                'Jabatan' => Position::query()->count(),
            ],
        ]);
    }

    public function roleLabel(string $role): string
    {
        return match ($role) {
            UserRole::Admin->value => 'Admin',
            UserRole::Pengurus->value => 'Pengurus',
            default => 'Anggota',
        };
    }
}
