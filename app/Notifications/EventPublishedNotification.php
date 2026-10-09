<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Kegiatan baru dipublikasikan — broadcast database saja (tanpa email massal).
 */
class EventPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $eventTitle,
        public string $eventSlug,
        public ?string $startsAtLabel = null,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'event_published',
            'title' => 'Kegiatan baru: '.$this->eventTitle,
            'body' => $this->startsAtLabel
                ? 'Dijadwalkan mulai '.$this->startsAtLabel
                : 'Kegiatan telah dipublikasikan.',
            'url' => route('public.events.show', $this->eventSlug),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kegiatan baru: '.$this->eventTitle)
            ->line($this->eventTitle)
            ->action('Lihat Kegiatan', route('public.events.show', $this->eventSlug));
    }
}
