<?php

namespace App\Livewire\Members;

use App\Actions\Members\CreateMember;
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
class MemberCreate extends Component
{
    use AuthorizesRequests;

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

    public function mount(): void
    {
        $this->authorize('create', Member::class);

        $this->joined_at = now()->toDateString();
        $this->batch_id = Batch::orderByDesc('year')->first()?->id;
        $this->class_id = SchoolClass::orderBy('name')->first()?->id;
        $this->position_id = Position::orderBy('level')->first()?->id;
    }

    public function save(CreateMember $createMember)
    {
        $data = $this->validatedData();

        $createMember->execute($data);

        session()->flash('success', 'Anggota berhasil ditambahkan.');

        return redirect()->route('members.index');
    }

    private function validatedData(): array
    {
        $request = MemberRequest::create('/', 'POST', [
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
        return view('livewire.members.member-create', [
            'genders' => Gender::cases(),
            'statuses' => MemberStatus::cases(),
            'batches' => Batch::orderByDesc('year')->get(),
            'classes' => SchoolClass::orderBy('name')->get(),
            'positions' => Position::orderBy('level')->get(),
        ]);
    }
}
