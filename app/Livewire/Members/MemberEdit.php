<?php

namespace App\Livewire\Members;

use App\Actions\Members\UpdateMember;
use App\Enums\Gender;
use App\Enums\MemberStatus;
use App\Http\Requests\MemberRequest;
use App\Models\Batch;
use App\Models\Member;
use App\Models\Position;
use App\Models\SchoolClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class MemberEdit extends Component
{
    use AuthorizesRequests;

    public Member $member;

    public string $full_name = '';

    public string $gender = 'laki';

    public string $nis = '';

    public string $nisn = '';

    public string $birth_date = '';

    public string $birth_place = '';

    public string $address = '';

    public string $phone = '';

    public string $email_pribadi = '';

    public string $parent_name = '';

    public string $parent_phone = '';

    public string $blood_type = '';

    public $batch_id = null;

    public $class_id = null;

    public $position_id = null;

    public string $status = 'aktif';

    public string $joined_at = '';

    public string $notes = '';

    public function mount(Member $member): void
    {
        $this->authorize('update', $member);

        $this->member = $member;
        $this->full_name = $member->full_name;
        $this->gender = $member->gender->value;
        $this->nis = $member->nis ?? '';
        $this->nisn = $member->nisn ?? '';
        $this->birth_date = $member->birth_date?->toDateString() ?? '';
        $this->birth_place = $member->birth_place ?? '';
        $this->address = $member->address ?? '';
        $this->phone = $member->phone ?? '';
        $this->email_pribadi = $member->email_pribadi ?? '';
        $this->parent_name = $member->parent_name ?? '';
        $this->parent_phone = $member->parent_phone ?? '';
        $this->blood_type = $member->blood_type ?? '';
        $this->batch_id = $member->batch_id;
        $this->class_id = $member->class_id;
        $this->position_id = $member->position_id;
        $this->status = $member->status->value;
        $this->joined_at = $member->joined_at?->toDateString() ?? now()->toDateString();
        $this->notes = $member->notes ?? '';
    }

    public function save(UpdateMember $updateMember)
    {
        $data = $this->validatedData();

        $updateMember->execute($this->member, $data);

        session()->flash('success', 'Data anggota berhasil diperbarui.');

        return redirect()->route('members.index');
    }

    private function validatedData(): array
    {
        $request = MemberRequest::create('/', 'POST', [
            'id' => $this->member->id,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'nis' => $this->nis ?: null,
            'nisn' => $this->nisn ?: null,
            'birth_date' => $this->birth_date ?: null,
            'birth_place' => $this->birth_place ?: null,
            'address' => $this->address ?: null,
            'phone' => $this->phone ?: null,
            'email_pribadi' => $this->email_pribadi ?: null,
            'parent_name' => $this->parent_name ?: null,
            'parent_phone' => $this->parent_phone ?: null,
            'blood_type' => $this->blood_type ?: null,
            'batch_id' => $this->batch_id,
            'class_id' => $this->class_id,
            'position_id' => $this->position_id,
            'status' => $this->status,
            'joined_at' => $this->joined_at,
            'notes' => $this->notes ?: null,
        ]);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        return $request->validated();
    }

    public function render()
    {
        return view('livewire.members.member-edit', [
            'genders' => Gender::cases(),
            'statuses' => MemberStatus::cases(),
            'batches' => Batch::orderByDesc('year')->get(),
            'classes' => SchoolClass::orderBy('name')->get(),
            'positions' => Position::orderBy('level')->get(),
        ]);
    }
}
