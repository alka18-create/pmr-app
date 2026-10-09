<?php

namespace App\Livewire\Users;

use App\Actions\Users\CreateUser;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class UserCreate extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'anggota';

    public string $status = 'active';

    public function mount(): void
    {
        $this->authorize('create', User::class);
    }

    public function save(CreateUser $createUser)
    {
        $data = $this->validatedData();

        $createUser->execute($data);

        session()->flash('success', 'Pengguna baru berhasil dibuat.');

        return redirect()->route('admin.users.index');
    }

    private function validatedData(): array
    {
        $request = UserRequest::create('/', 'POST', [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
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
        return view('livewire.users.user-create', [
            'roles' => UserRole::cases(),
            'statuses' => UserStatus::cases(),
        ]);
    }
}
