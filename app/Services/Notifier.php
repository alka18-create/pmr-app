<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Enums\UserStatus;
use App\Models\Member;
use App\Models\User;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Pusat pengiriman notifikasi (docs/13 §7.4).
 * Selalu saring penerima: hanya user aktif; broadcast hanya member aktif.
 */
class Notifier
{
    /**
     * Kirim ke akun login terkait member (bila ada & aktif).
     */
    public function toMember(?Member $member, BaseNotification $notification): bool
    {
        $member?->loadMissing('user');
        $user = $member?->user;

        if (! $user || $user->status !== UserStatus::Active) {
            return false;
        }

        $user->notify($notification);

        return true;
    }

    /**
     * Broadcast ke semua user aktif yang punya profil member aktif.
     *
     * @return int jumlah penerima
     */
    public function toActiveMembers(BaseNotification $notification): int
    {
        $users = User::query()
            ->where('status', UserStatus::Active)
            ->whereHas('member', fn ($q) => $q->where('status', MemberStatus::Aktif->value))
            ->get();

        NotificationFacade::send($users, $notification);

        return $users->count();
    }
}
