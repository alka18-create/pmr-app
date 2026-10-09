<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sertifikat siap — database + email (queue default).
 */
class CertificateReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $certificateTitle,
        public string $certificateCode,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'certificate_ready',
            'title' => 'Sertifikat siap diunduh',
            'body' => $this->certificateTitle.' (kode: '.$this->certificateCode.')',
            'url' => route('certificates.mine'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Sertifikat siap: '.$this->certificateTitle)
            ->line($this->certificateTitle)
            ->line('Kode: '.$this->certificateCode)
            ->action('Lihat Sertifikat', route('certificates.mine'));
    }
}
