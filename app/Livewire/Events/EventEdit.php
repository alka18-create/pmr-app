<?php

namespace App\Livewire\Events;

use App\Actions\Events\UpdateEvent;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.panel')]
class EventEdit extends Component
{
    use AuthorizesRequests, WithFileUploads;

    #[Locked]
    public int $eventId;

    public string $title = '';

    public string $type = 'lainnya';

    public string $description = '';

    public string $location = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public $cover = null;

    public string $status = 'draft';

    public bool $is_public = true;

    public $max_participants = null;

    public function mount(Event $event): void
    {
        $this->authorize('update', $event);

        $this->eventId = $event->id;
        $this->title = $event->title;
        $this->type = $event->type->value;
        $this->description = (string) $event->description;
        $this->location = (string) $event->location;
        $this->starts_at = $event->starts_at->format('Y-m-d\TH:i');
        $this->ends_at = $event->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->status = $event->status->value;
        $this->is_public = (bool) $event->is_public;
        $this->max_participants = $event->max_participants;
    }

    public function save(UpdateEvent $updateEvent)
    {
        $rules = [
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', Rule::enum(EventType::class)],
            'description' => ['nullable', 'string', 'max:10000'],
            'location' => ['nullable', 'string', 'max:200'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::enum(EventStatus::class)],
            'is_public' => ['boolean'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];

        if ($this->cover) {
            $rules['cover'] = ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
        }

        $validated = $this->validate($rules, [
            'title.required' => 'Judul kegiatan wajib diisi.',
            'type.required' => 'Jenis kegiatan wajib dipilih.',
            'starts_at.required' => 'Waktu mulai wajib diisi.',
            'ends_at.after_or_equal' => 'Waktu selesai tidak boleh sebelum waktu mulai.',
            'status.required' => 'Status wajib dipilih.',
            'cover.mimes' => 'Poster harus berformat JPG, PNG, atau WebP.',
            'cover.max' => 'Poster maksimal 2 MB.',
        ]);

        $event = Event::findOrFail($this->eventId);
        $this->authorize('update', $event);

        $data = [
            'title' => $validated['title'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?? null,
            'status' => $validated['status'],
            'is_public' => (bool) ($validated['is_public'] ?? $this->is_public),
            'max_participants' => $validated['max_participants'] ?? null,
        ];

        $cover = $this->cover instanceof \Illuminate\Http\UploadedFile ? $this->cover : null;
        $updateEvent->execute($event, $data, $cover);

        session()->flash('success', 'Kegiatan berhasil diperbarui.');

        return redirect()->route('events.show', $event);
    }

    public function render()
    {
        return view('livewire.events.event-edit', [
            'types' => EventType::cases(),
            'statuses' => EventStatus::cases(),
        ]);
    }
}
