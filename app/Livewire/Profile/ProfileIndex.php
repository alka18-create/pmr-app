<?php

namespace App\Livewire\Profile;

use App\Actions\Profile\UpdatePassword;
use App\Actions\Profile\UpdateProfile;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.panel', ['title' => 'Profil Saya'])]
class ProfileIndex extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $email = '';

    #[Locked]
    public string $role = '';

    // Ganti Password
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Data Anggota (bila akun terhubung ke Member)
    #[Locked]
    public bool $hasMember = false;

    #[Locked]
    public string $nis = '';

    #[Locked]
    public string $nisn = '';

    #[Locked]
    public string $birth_date = '';

    #[Locked]
    public string $birth_place = '';

    #[Locked]
    public string $gender = '';

    #[Locked]
    public string $blood_type = '';

    #[Locked]
    public string $batch_name = '';

    #[Locked]
    public string $class_name = '';

    #[Locked]
    public string $position_name = '';

    #[Locked]
    public string $status = '';

    // Data kontak anggota (bisa diubah mandiri)
    public string $address = '';

    public string $phone = '';

    public string $email_pribadi = '';

    public string $parent_name = '';

    public string $parent_phone = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;

        if ($user->member) {
            $member = $user->member;
            $member->loadMissing(['batch', 'schoolClass', 'position']);

            $this->hasMember = true;
            $this->nis = (string) $member->nis;
            $this->nisn = (string) $member->nisn;
            $this->birth_date = $member->birth_date ? $member->birth_date->format('d/m/Y') : '-';
            $this->birth_place = (string) $member->birth_place;
            $this->gender = $member->gender === \App\Enums\Gender::Laki ? 'Laki-laki' : ($member->gender === \App\Enums\Gender::Perempuan ? 'Perempuan' : '-');
            $this->blood_type = (string) ($member->blood_type ?? '-');
            $this->batch_name = (string) ($member->batch?->name ?? '-');
            $this->class_name = (string) ($member->schoolClass?->name ?? '-');
            $this->position_name = (string) ($member->position?->name ?? '-');
            $this->status = ucfirst((string) ($member->status?->value ?? '-'));

            $this->address = (string) ($member->address ?? '');
            $this->phone = (string) ($member->phone ?? '');
            $this->email_pribadi = (string) ($member->email_pribadi ?? '');
            $this->parent_name = (string) ($member->parent_name ?? '');
            $this->parent_phone = (string) ($member->parent_phone ?? '');
        }
    }

    public function saveProfile(UpdateProfile $updater): void
    {
        /** @var User $user */
        $user = auth()->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        if ($this->hasMember) {
            $rules['address'] = ['nullable', 'string', 'max:500'];
            $rules['phone'] = ['nullable', 'string', 'max:20'];
            $rules['email_pribadi'] = ['nullable', 'email', 'max:255'];
            $rules['parent_name'] = ['nullable', 'string', 'max:255'];
            $rules['parent_phone'] = ['nullable', 'string', 'max:20'];
        }

        $validated = $this->validate($rules, [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan pengguna lain.',
        ]);

        $updater->execute($user, $validated);

        session()->flash('success', 'Profil Anda berhasil diperbarui.');
    }

    public function savePassword(UpdatePassword $updater): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $updater->execute($user, $this->current_password, $this->password);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('success', 'Password berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.profile.profile-index');
    }
}
