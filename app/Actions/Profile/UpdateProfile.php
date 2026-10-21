<?php

namespace App\Actions\Profile;

use App\Models\User;
use App\Services\AuditLogger;

class UpdateProfile
{
    /**
     * Perbarui data profil akun dan data kontak anggota (bila ada).
     *
     * @param  array{name: string, email: string, address?: ?string, phone?: ?string, email_pribadi?: ?string, parent_name?: ?string, parent_phone?: ?string}  $data
     */
    public function execute(User $user, array $data): User
    {
        $oldUser = $user->only(['name', 'email']);
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        app(AuditLogger::class)->log('update', $user, $oldUser, $user->only(['name', 'email']), $user);

        if ($user->member) {
            $member = $user->member;
            $fields = ['address', 'phone', 'email_pribadi', 'parent_name', 'parent_phone'];
            $oldMember = $member->only($fields);

            $contactData = array_intersect_key($data, array_flip($fields));
            if (! empty($contactData)) {
                $member->update($contactData);
                app(AuditLogger::class)->log('update', $member, $oldMember, $member->only($fields), $user);
            }
        }

        return $user;
    }
}
