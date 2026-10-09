<?php

namespace Tests\Feature;

use App\Actions\Certificates\IssueCertificate;
use App\Actions\Events\UpdateEvent;
use App\Actions\Quizzes\StartQuizAttempt;
use App\Actions\Quizzes\SubmitQuizAttempt;
use App\Enums\CertificateSourceType;
use App\Enums\EventStatus;
use App\Enums\MemberStatus;
use App\Enums\UserRole;
use App\Livewire\Notifications\NotificationsBadge;
use App\Livewire\Notifications\NotificationsIndex;
use App\Models\Event;
use App\Models\Member;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Notifications\CertificateReadyNotification;
use App\Notifications\EventPublishedNotification;
use App\Notifications\QuizResultPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function anggota(): User
    {
        return User::factory()->create(['role' => UserRole::Anggota]);
    }

    private function makeQuiz(array $overrides = []): Quiz
    {
        $quiz = Quiz::factory()->create($overrides);

        $question = QuizQuestion::factory()->create([
            'quiz_id' => $quiz->id,
            'question' => 'Soal 1?',
            'score' => 10,
            'order' => 0,
        ]);

        QuizOption::factory()->correct()->create([
            'question_id' => $question->id,
            'option_text' => 'Benar',
            'order' => 0,
        ]);

        QuizOption::factory()->create([
            'question_id' => $question->id,
            'option_text' => 'Salah',
            'order' => 1,
        ]);

        return $quiz->fresh(['questions.options']);
    }

    public function test_event_dipublish_membuat_notifikasi_untuk_anggota_aktif(): void
    {
        $aktifUser = $this->anggota();
        Member::factory()->create(['user_id' => $aktifUser->id, 'status' => MemberStatus::Aktif]);

        $nonaktifUser = $this->anggota();
        Member::factory()->create(['user_id' => $nonaktifUser->id, 'status' => MemberStatus::Nonaktif]);

        Member::factory()->create(['user_id' => null, 'status' => MemberStatus::Aktif]);

        $event = Event::factory()->draft()->create();

        (new UpdateEvent)->execute($event, ['status' => EventStatus::Published]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $aktifUser->id,
            'type' => EventPublishedNotification::class,
        ]);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $nonaktifUser->id,
            'type' => EventPublishedNotification::class,
        ]);

        $this->assertSame(1, DB::table('notifications')->count());
    }

    public function test_update_event_tanpa_ubah_status_tidak_membuat_notifikasi(): void
    {
        $user = $this->anggota();
        Member::factory()->create(['user_id' => $user->id, 'status' => MemberStatus::Aktif]);

        $event = Event::factory()->published()->create();

        (new UpdateEvent)->execute($event, ['location' => 'Aula Baru']);

        $this->assertSame(0, DB::table('notifications')->count());
    }

    public function test_nilai_quiz_terbit_membuat_notifikasi_ke_pemilik(): void
    {
        $user = $this->anggota();
        $member = Member::factory()->create(['user_id' => $user->id, 'status' => MemberStatus::Aktif]);
        $quiz = $this->makeQuiz(['max_attempts' => 3, 'pass_score' => 70]);

        $attempt = (new StartQuizAttempt)->execute($quiz, $member->id, '127.0.0.1');
        $question = $quiz->questions->first();
        $correctId = $question->options->firstWhere('is_correct', true)->id;

        (new SubmitQuizAttempt)->execute($attempt, [$question->id => $correctId]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => QuizResultPublishedNotification::class,
        ]);
    }

    public function test_sertifikat_terbit_membuat_notifikasi_ke_pemilik(): void
    {
        $user = $this->anggota();
        $member = Member::factory()->create(['user_id' => $user->id, 'status' => MemberStatus::Aktif]);

        (new IssueCertificate)->execute(
            $member->id,
            CertificateSourceType::Quiz,
            null,
            'Sertifikat Juara',
        );

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => CertificateReadyNotification::class,
        ]);
    }

    public function test_halaman_notifikasi_hanya_menampilkan_milik_sendiri(): void
    {
        $userA = $this->anggota();
        $userB = $this->anggota();

        $userA->notify(new CertificateReadyNotification('Sertifikat A', 'CODE-A'));
        $userB->notify(new CertificateReadyNotification('Sertifikat B', 'CODE-B'));

        Livewire::actingAs($userA)
            ->test(NotificationsIndex::class)
            ->assertSee('Sertifikat A')
            ->assertDontSee('Sertifikat B');
    }

    public function test_tandai_sudah_dibaca(): void
    {
        $user = $this->anggota();
        $user->notify(new CertificateReadyNotification('Sertifikat X', 'CODE-X'));
        $user->notify(new CertificateReadyNotification('Sertifikat Y', 'CODE-Y'));

        Livewire::actingAs($user)
            ->test(NotificationsIndex::class)
            ->call('markAllRead')
            ->assertSee('0 notifikasi belum dibaca');

        $this->assertSame(0, $user->unreadNotifications()->count());

        // markRead per item tetap aman (scoped ke user sendiri)
        $user->notifications()->update(['read_at' => null]);
        $id = $user->notifications()->first()->id;

        Livewire::actingAs($user)
            ->test(NotificationsIndex::class)
            ->call('markRead', $id);

        $this->assertSame(1, $user->unreadNotifications()->count());
        $this->assertNotNull($user->notifications()->find($id)->read_at);
    }

    public function test_badge_menampilkan_jumlah_unread(): void
    {
        $user = $this->anggota();
        $user->notify(new CertificateReadyNotification('S1', 'C1'));
        $user->notify(new CertificateReadyNotification('S2', 'C2'));

        Livewire::actingAs($user)
            ->test(NotificationsBadge::class)
            ->assertSee('2');
    }

    public function test_route_notifikasi_login_wajib_tamu_dialihkan(): void
    {
        $this->get(route('notifications.index'))->assertRedirect('/login');
    }
}
