<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Masters\BatchIndex;
use App\Livewire\Masters\ClassIndex;
use App\Livewire\Masters\PositionIndex;
use App\Livewire\Members\MemberEdit;
use App\Livewire\Members\MemberShow;
use App\Livewire\Programs\ProgramIndex;
use App\Livewire\Settings\OrganizationProfile;
use App\Livewire\Settings\SettingIndex;
use App\Livewire\Users\UserEdit;
use App\Livewire\Website\CertificateVerify;
use App\Models\Batch;
use App\Models\Certificate;
use App\Models\Member;
use App\Models\Position;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * LivewireLockedPropertiesTest — menjamin properti state internal/sensitif
 * pada komponen Livewire tidak dapat dimanipulasi dari sisi client.
 *
 * Sesuai docs/08 §2.2 & docs/05 §6.
 */
class LivewireLockedPropertiesTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_batch_edit_id_is_locked(): void
    {
        $this->actingAs($this->createAdmin());

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(BatchIndex::class)
            ->set('editId', 999);
    }

    public function test_class_edit_id_is_locked(): void
    {
        $this->actingAs($this->createAdmin());

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(ClassIndex::class)
            ->set('editId', 999);
    }

    public function test_position_edit_id_is_locked(): void
    {
        $this->actingAs($this->createAdmin());

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(PositionIndex::class)
            ->set('editId', 999);
    }

    public function test_program_edit_id_is_locked(): void
    {
        $this->actingAs($this->createAdmin());

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(ProgramIndex::class)
            ->set('editId', 999);
    }

    public function test_setting_edit_id_is_locked(): void
    {
        $this->actingAs($this->createAdmin());

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(SettingIndex::class)
            ->set('editId', 999);
    }

    public function test_member_show_internal_properties_are_locked(): void
    {
        $member = Member::factory()->create();

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(MemberShow::class, ['member' => $member])
            ->set('showInternal', true);
    }

    public function test_member_edit_model_is_locked(): void
    {
        $this->actingAs($this->createAdmin());
        $member1 = Member::factory()->create();
        $member2 = Member::factory()->create();

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(MemberEdit::class, ['member' => $member1])
            ->set('member', $member2);
    }

    public function test_user_edit_model_is_locked(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(UserEdit::class, ['user' => $user1])
            ->set('user', $user2);
    }

    public function test_certificate_verify_certificate_is_locked(): void
    {
        $cert = Certificate::factory()->create();

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(CertificateVerify::class)
            ->set('certificate', $cert);
    }

    public function test_organization_profile_logo_path_is_locked(): void
    {
        $this->actingAs($this->createAdmin());

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(OrganizationProfile::class)
            ->set('logoPath', 'malicious/path.jpg');
    }

    public function test_profile_role_is_locked(): void
    {
        $user = User::factory()->create(['role' => UserRole::Anggota]);
        $this->actingAs($user);

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(\App\Livewire\Profile\ProfileIndex::class)
            ->set('role', 'admin');
    }

    public function test_reset_password_token_is_locked(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(\App\Livewire\Auth\ResetPassword::class, ['token' => 'initial-token'])
            ->set('token', 'hacked-token');
    }
}
