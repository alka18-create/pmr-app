<?php

namespace App\Livewire\Messages;

use App\Enums\MessageStatus;
use App\Models\Article;
use App\Models\Message;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class MessageIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $statusFilter = '';

    #[Locked]
    public ?int $openId = null;

    public function mount(): void
    {
        $this->authorize('create', Article::class);
    }

    public function updated(string $property): void
    {
        if ($property === 'statusFilter') {
            $this->resetPage();
        }
    }

    public function open(int $id, \App\Actions\Messages\SetMessageStatus $setStatus): void
    {
        $message = Message::findOrFail($id);

        if ($message->status === MessageStatus::Unread) {
            $setStatus->execute($message, MessageStatus::Read);
        }

        $this->openId = $id;
    }

    public function close(): void
    {
        $this->openId = null;
    }

    public function markReplied(int $id, \App\Actions\Messages\SetMessageStatus $setStatus): void
    {
        $message = Message::findOrFail($id);
        $setStatus->execute($message, MessageStatus::Replied);
        session()->flash('success', 'Pesan ditandai sudah dibalas.');
        $this->openId = null;
    }

    public function render()
    {
        $messages = Message::query()
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        $open = $this->openId ? Message::find($this->openId) : null;

        return view('livewire.messages.message-index', [
            'messages' => $messages,
            'open' => $open,
            'statuses' => MessageStatus::cases(),
            'unreadCount' => Message::where('status', MessageStatus::Unread)->count(),
        ]);
    }
}
