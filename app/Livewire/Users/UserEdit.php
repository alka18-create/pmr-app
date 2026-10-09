<?php

namespace App\Livewire\Users;

use App\Actions\Users\UpdateUser;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class UserEdit extends Component
{
    use AuthorizesRequests;

    public User $user;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'anggota';

    public string $status = 'active';

    public function mount(User $user): void
    {
        $this->authorize('update', $user);

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->status = $user->status->value;
    }

    public function save(UpdateUser $updateUser)
    {
        $data = $this->validatedData();

        $updateUser->execute($this->user, $data);

        session()->flash('success', 'Pengguna berhasil diperbarui.');

        return redirect()->route('admin.users.index');
    }

    private function validatedData(): array
    {
        $request = UserRequest::create('/', 'POST', [
            'id' => $this->user->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password !== '' ? $this->password : null,
            'role' => $this->role,
            'status' => $this->status,
        ]);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        return $request->validated();
    }

    public function render()
    {
        return view('livewire.users.user-edit', [
            'roles' => UserRole::cases(),
            'statuses' => UserStatus::cases(),
        ]);
    }
}
