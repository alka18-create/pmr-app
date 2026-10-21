<?php

namespace App\Livewire\Events;

use App\Actions\Attendance\RecordAttendance;
use App\Actions\Events\DeleteEvent;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\EventDocument;
use App\Models\EventParticipant;
use App\Models\Member;
use App\Models\SchoolClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class EventShow extends Component
{
    use AuthorizesRequests, WithFileUploads, WithPagination;

    #[Locked]
    public int $eventId;

    public string $memberSearch = '';

    public string $attendanceMemberId = '';

    public string $attendanceStatus = 'hadir';

    public $attendanceNotes = '';

    public $documentFile = null;

    public string $documentCaption = '';

    public bool $documentIsPublic = false;

    public string $bulkClassId = '';

    public function mount(Event $event): void
    {
        $this->authorize('view', $event);
        $this->eventId = $event->id;
    }

    public function delete(DeleteEvent $deleteEvent)
    {
        $event = Event::findOrFail($this->eventId);
        $this->authorize('delete', $event);

        $deleteEvent->execute($event);

        session()->flash('success', 'Kegiatan dihapus.');

        return redirect()->route('events.index');
    }

    public function addParticipant()
    {
        $event = Event::findOrFail($this->eventId);
        $this->authorize('update', $event);

        $memberId = (int) $this->memberSearch;
        if ($memberId <= 0) {
            session()->flash('error', 'Pilih anggota yang valid.');

            return;
        }

        $member = Member::find($memberId);
        if (! $member) {
            session()->flash('error', 'Anggota tidak ditemukan.');

            return;
        }

        $participant = app(\App\Actions\Events\AddEventParticipant::class)->execute($event, $member);

        if (! $participant) {
            session()->flash('error', 'Anggota sudah menjadi peserta.');

            return;
        }

        session()->flash('success', 'Peserta ditambahkan.');
    }

    public function addParticipantsByClass(\App\Actions\Events\AddEventParticipant $addParticipant)
    {
        $event = Event::findOrFail($this->eventId);
        $this->authorize('update', $event);

        $classId = (int) $this->bulkClassId;
        if ($classId <= 0) {
            session()->flash('error', 'Pilih kelas yang valid.');

            return;
        }

        $added = $addParticipant->bulkByClass($event, $classId);

        session()->flash(
            $added > 0 ? 'success' : 'error',
            $added > 0 ? $added.' peserta ditambahkan.' : 'Semua anggota kelas sudah menjadi peserta atau kelas kosong.'
        );
    }

    public function removeParticipant(int $participantId, \App\Actions\Events\RemoveEventParticipant $removeParticipant)
    {
        $event = Event::findOrFail($this->eventId);
        $this->authorize('update', $event);

        $removeParticipant->execute($event, $participantId);

        session()->flash('success', 'Peserta dihapus.');
    }

    public function saveAttendance(RecordAttendance $recordAttendance)
    {
        $event = Event::findOrFail($this->eventId);
        $this->authorize('manage', AttendanceRecord::class);

        $validated = $this->validate([
            'attendanceMemberId' => ['required', 'integer', Rule::exists('members', 'id')],
            'attendanceStatus' => ['required', Rule::enum(AttendanceStatus::class)],
            'attendanceNotes' => ['nullable', 'string', 'max:500'],
        ], [
            'attendanceMemberId.required' => 'Anggota wajib dipilih.',
            'attendanceMemberId.exists' => 'Anggota tidak ditemukan.',
            'attendanceStatus.required' => 'Status absensi wajib dipilih.',
            'attendanceStatus.enum' => 'Status absensi tidak valid.',
        ]);

        $member = Member::findOrFail($validated['attendanceMemberId']);

        $recordAttendance->execute($event, $member, [
            'status' => $validated['attendanceStatus'],
            'notes' => $validated['attendanceNotes'] ?? null,
            'method' => 'manual',
            'checked_in_by' => auth()->id(),
        ]);

        session()->flash('success', 'Absensi disimpan.');
        $this->reset(['attendanceMemberId', 'attendanceNotes']);
        $this->attendanceStatus = 'hadir';
    }

    public function uploadDocument(\App\Actions\Events\UploadEventDocument $uploader)
    {
        $event = Event::findOrFail($this->eventId);
        $this->authorize('update', $event);

        $this->validate([
            'documentFile' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4', 'max:5120'],
            'documentCaption' => ['nullable', 'string', 'max:500'],
        ], [
            'documentFile.mimes' => 'File harus berformat JPG, PNG, WebP, atau MP4.',
            'documentFile.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $uploader->execute($event, $this->documentFile, [
            'caption' => $this->documentCaption ?: null,
            'is_public' => $this->documentIsPublic,
            'uploaded_by' => auth()->id(),
        ]);

        session()->flash('success', 'Dokumentasi diunggah.');
        $this->reset(['documentFile', 'documentCaption']);
        $this->documentIsPublic = false;
    }

    public function render()
    {
        $event = Event::withCount('participants')->findOrFail($this->eventId);

        $participants = EventParticipant::with(['member:id,full_name,slug', 'member.schoolClass:id,name'])
            ->where('event_id', $event->id)
            ->paginate(10);

        $attendance = AttendanceRecord::with('member:id,full_name')
            ->where('event_id', $event->id)
            ->paginate(10, ['*'], 'attendancePage');

        $members = Member::query()
            ->select(['id', 'full_name'])
            ->orderBy('full_name')
            ->get();

        $participantMemberIds = EventParticipant::where('event_id', $event->id)
            ->pluck('member_id');

        $documents = EventDocument::where('event_id', $event->id)
            ->latest()
            ->get();

        return view('livewire.events.event-show', [
            'event' => $event,
            'participants' => $participants,
            'attendance' => $attendance,
            'members' => $members,
            'attendanceStatuses' => AttendanceStatus::cases(),
            'participantMemberIds' => $participantMemberIds,
            'documents' => $documents,
            'classes' => SchoolClass::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
