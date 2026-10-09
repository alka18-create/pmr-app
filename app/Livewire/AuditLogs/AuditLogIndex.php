<?php

namespace App\Livewire\AuditLogs;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class AuditLogIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $actionFilter = '';

    public string $userFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $this->authorize('viewAny', AuditLog::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'actionFilter', 'userFilter', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(model_type) LIKE ?', [$term])
                        ->orWhere('model_id', 'like', $this->search.'%');
                });
            })
            ->when($this->actionFilter !== '', fn ($query) => $query->where('action', $this->actionFilter))
            ->when($this->userFilter !== '', fn ($query) => $query->where('user_id', $this->userFilter))
            ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->latest('created_at')
            ->paginate(15);

        return view('livewire.audit-logs.audit-log-index', [
            'logs' => $logs,
            'actions' => AuditAction::cases(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
