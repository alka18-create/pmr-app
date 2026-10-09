<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Nilai quiz terbit — database + email (queue default).
 */
class QuizResultPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $quizTitle,
        public int $score,
        public bool $passed,
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
            'kind' => 'quiz_result',
            'title' => 'Nilai quiz telah terbit',
            'body' => sprintf(
                '%s — skor %d (%s)',
                $this->quizTitle,
                $this->score,
                $this->passed ? 'LULUS' : 'BELUM LULUS',
            ),
            'url' => route('results.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nilai quiz telah terbit: '.$this->quizTitle)
            ->line('Quiz: '.$this->quizTitle)
            ->line('Skor: '.$this->score.' — '.($this->passed ? 'LULUS' : 'BELUM LULUS'))
            ->action('Lihat Nilai', route('results.index'));
    }
}
