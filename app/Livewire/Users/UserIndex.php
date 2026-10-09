<?php

namespace App\Livewire\Users;

use App\Actions\Users\DeleteUser;
use App\Actions\Users\SetUserStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class UserIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'roleFilter', 'statusFilter'], true)) {
            $this->resetPage();
        }
    }

    public function toggleStatus(User $user, SetUserStatus $setStatus)
    {
        $this->authorize('update', $user);

        $newStatus = $user->status === UserStatus::Active
            ? UserStatus::Inactive
            : UserStatus::Active;

        $setStatus->execute($user, $newStatus);

        session()->flash('success', sprintf(
            'Status %s diubah menjadi %s.',
            $user->name,
            $newStatus->value,
        ));

        return redirect()->route('admin.users.index');
    }

    public function delete(User $user, DeleteUser $deleteUser)
    {
        $this->authorize('delete', $user);

        try {
            $deleteUser->execute($user, auth()->user());
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->route('admin.users.index');
        }

        session()->flash('success', sprintf('Pengguna %s dihapus.', $user->name));

        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                });
            })
            ->when($this->roleFilter !== '', fn ($query) => $query->where('role', $this->roleFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.users.user-index', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'statuses' => UserStatus::cases(),
        ]);
    }
}
