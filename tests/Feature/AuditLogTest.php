<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Livewire\AuditLogs\AuditLogIndex;
use App\Livewire\Users\UserCreate;
use App\Livewire\Users\UserEdit;
use App\Livewire\Users\UserIndex;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_admin_dapat_membuka_halaman_audit_log(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.audit-log.index'))
            ->assertOk()
            ->assertSee('Audit Log');
    }

    public function test_non_admin_ditolak_membuka_halaman_audit_log(): void
    {
        foreach ([UserRole::Pengurus, UserRole::Anggota] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('admin.audit-log.index'))->assertForbidden();
        }
    }

    public function test_aksi_update_user_menghasilkan_1_baris_audit_dengan_old_new_values(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['name' => 'Nama Lama', 'role' => UserRole::Anggota]);

        Livewire::actingAs($admin)
            ->test(UserEdit::class, ['user' => $user])
            ->set('name', 'Nama Baru')
            ->set('role', 'pengurus')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, AuditLog::where('action', AuditAction::Updated->value)->count());

        $log = AuditLog::where('action', AuditAction::Updated->value)->firstOrFail();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame(User::class, $log->model_type);
        $this->assertSame($user->id, $log->model_id);
        $this->assertSame('Nama Lama', $log->old_values['name']);
        $this->assertSame('Nama Baru', $log->new_values['name']);
        $this->assertSame('anggota', $log->old_values['role']);
        $this->assertSame('pengurus', $log->new_values['role']);
        $this->assertArrayNotHasKey('password', $log->old_values);
        $this->assertArrayNotHasKey('password', $log->new_values);
    }

    public function test_aksi_create_user_menghasilkan_audit_log(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(UserCreate::class)
            ->set('name', 'Pengguna Baru')
            ->set('email', 'baru@pmr.test')
            ->set('password', 'rahasia123')
            ->set('role', 'anggota')
            ->call('save')
            ->assertHasNoErrors();

        $log = AuditLog::where('action', AuditAction::Created->value)->firstOrFail();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('Pengguna Baru', $log->new_values['name']);
        $this->assertArrayNotHasKey('password', $log->new_values);
    }

    public function test_aksi_delete_user_menghasilkan_audit_log(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['name' => 'Akan Dihapus']);

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('delete', $target)
            ->assertSessionHas('success');

        $log = AuditLog::where('action', AuditAction::Deleted->value)->firstOrFail();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame($target->id, $log->model_id);
        $this->assertSame('Akan Dihapus', $log->old_values['name']);
        $this->assertArrayNotHasKey('password', $log->old_values);
    }

    public function test_login_menghasilkan_audit_log(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('panel.home'));

        $log = AuditLog::where('action', AuditAction::Login->value)->firstOrFail();

        $this->assertSame($user->id, $log->user_id);
        $this->assertSame(User::class, $log->model_type);
        $this->assertSame($user->id, $log->model_id);
    }

    public function test_toggle_status_user_menghasilkan_audit_log(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('toggleStatus', $user)
            ->assertSessionHas('success');

        $log = AuditLog::where('action', AuditAction::Updated->value)->firstOrFail();

        $this->assertSame('active', $log->old_values['status']);
        $this->assertSame('inactive', $log->new_values['status']);
    }

    public function test_filter_audit_log_per_aksi_dan_user(): void
    {
        $admin = $this->admin();
        $other = User::factory()->create(['role' => UserRole::Admin]);

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'updated',
            'model_type' => User::class,
            'model_id' => 1,
            'old_values' => ['name' => 'Sebelum'],
            'new_values' => ['name' => 'DiubahAdmin'],
            'ip_address' => '127.0.0.1',
        ]);

        AuditLog::create([
            'user_id' => $other->id,
            'action' => 'updated',
            'model_type' => User::class,
            'model_id' => 2,
            'old_values' => ['name' => 'Sebelum'],
            'new_values' => ['name' => 'DiubahOther'],
            'ip_address' => '127.0.0.1',
        ]);

        Livewire::actingAs($admin)
            ->test(AuditLogIndex::class)
            ->set('actionFilter', 'updated')
            ->set('userFilter', (string) $admin->id)
            ->assertSee('DiubahAdmin')
            ->assertDontSee('DiubahOther');

        Livewire::actingAs($admin)
            ->test(AuditLogIndex::class)
            ->set('actionFilter', 'created')
            ->assertDontSee('DiubahAdmin')
            ->assertDontSee('DiubahOther');
    }

    public function test_password_tidak_pernah_tersimpan_di_audit_log(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(UserEdit::class, ['user' => $user])
            ->set('name', 'Ganti Password')
            ->set('password', 'barubaru123')
            ->call('save')
            ->assertHasNoErrors();

        foreach (AuditLog::all() as $log) {
            $this->assertArrayNotHasKey('password', $log->old_values ?? []);
            $this->assertArrayNotHasKey('password', $log->new_values ?? []);
        }

        $log = AuditLog::where('action', AuditAction::Updated->value)
            ->where('model_id', $user->id)
            ->firstOrFail();

        $this->assertTrue($log->new_values['password_changed'] ?? false);
    }

    public function test_logout_menghasilkan_audit_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $log = AuditLog::where('action', AuditAction::Logout->value)->first();
        $this->assertNotNull($log);
        $this->assertSame($user->id, $log->user_id);
    }

    public function test_master_data_crud_menghasilkan_audit_log(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Masters\BatchIndex::class)
            ->set('name', 'Angkatan Audit')
            ->set('year', 2028)
            ->call('save')
            ->assertHasNoErrors();

        $batch = \App\Models\Batch::where('name', 'Angkatan Audit')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'model_type' => \App\Models\Batch::class,
            'model_id' => $batch->id,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Masters\BatchIndex::class)
            ->call('delete', $batch->id);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'model_type' => \App\Models\Batch::class,
            'model_id' => $batch->id,
        ]);
    }

    public function test_setting_crud_menghasilkan_audit_log(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Settings\SettingIndex::class)
            ->set('key', 'audit_setting_key')
            ->set('value', 'nilai')
            ->call('save')
            ->assertHasNoErrors();

        $setting = \App\Models\Setting::where('key', 'audit_setting_key')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'model_type' => \App\Models\Setting::class,
            'model_id' => $setting->id,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Settings\SettingIndex::class)
            ->call('delete', $setting);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'model_type' => \App\Models\Setting::class,
            'model_id' => $setting->id,
        ]);
    }
}
