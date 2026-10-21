<?php

namespace App\Actions\Messages;

use App\Enums\MessageStatus;
use App\Models\Message;

class SetMessageStatus
{
    /**
     * Perbarui status pesan masuk kontak.
     */
    public function execute(Message $message, MessageStatus $status): Message
    {
        $message->update(['status' => $status]);

        return $message;
    }
}
