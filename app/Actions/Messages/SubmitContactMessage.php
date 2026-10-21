<?php

namespace App\Actions\Messages;

use App\Enums\MessageStatus;
use App\Models\Message;

class SubmitContactMessage
{
    /**
     * Simpan pesan dari form kontak publik.
     *
     * @param  array{name: string, email: string, subject: string, body: string}  $data
     */
    public function execute(array $data, ?string $ip = null): Message
    {
        return Message::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'status' => MessageStatus::Unread,
            'ip_address' => $ip,
        ]);
    }
}
