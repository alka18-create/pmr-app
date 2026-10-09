<?php

namespace Tests\Feature;

use App\Actions\Quizzes\ExpireStaleQuizAttempts;
use App\Actions\Quizzes\GradeQuizAttempt;
use App\Actions\Quizzes\StartQuizAttempt;
use App\Actions\Quizzes\SubmitQuizAttempt;
use App\Enums\MaterialCategory;
use App\Enums\MaterialLevel;
use App\Enums\QuizAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Materials\MaterialBrowse;
use App\Livewire\Materials\MaterialIndex;
use App\Livewire\Quizzes\QuizCreate;
use App\Livewire\Quizzes\QuizIndex;
use App\Livewire\Quizzes\QuizTake;
use App\Models\Material;
use App\Models\Member;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AcademyQuizTest extends TestCase
{
    use RefreshDatabase;

    private function pengurus(): User
    {
        return User::factory()->create(['role' => UserRole::Pengurus]);
    }

    private function anggota(): User
    {
        return User::factory()->create(['role' => UserRole::Anggota]);
    }

    private function anggotaWithMember(): array
    {
        $user = $this->anggota();
        $member = Member::factory()->create(['user_id' => $user->id]);

        return [$user, $member];
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

        $correct = QuizOption::factory()->correct()->create([
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

    public function test_anggota_dapat_lihat_materi_tapi_tidak_manage(): void
    {
        $anggota = $this->anggota();

        $this->actingAs($anggota)->get(route('materials.index'))->assertOk();
        $this->actingAs($anggota)->get(route('materials.manage'))->assertForbidden();
    }

    public function test_pengurus_dapat_buat_materi_pdf(): void
    {
        Storage::fake('public');
        $pengurus = $this->pengurus();

        Livewire::actingAs($pengurus)
            ->test(MaterialIndex::class)
            ->set([
                'title' => 'Materi P3K Lanjutan',
                'category' => 'dasar_p3k',
                'level' => 'madya',
                'content' => 'Catatan singkat',
                'order' => 1,
                'is_public' => true,
                'file' => UploadedFile::fake()->create('materi.pdf', 100, 'application/pdf'),
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSessionHas('success');

        $material = Material::where('title', 'Materi P3K Lanjutan')->firstOrFail();

        $this->assertSame($pengurus->id, $material->uploaded_by);
        $this->assertSame(MaterialCategory::DasarP3k, $material->category);
        $this->assertSame(MaterialLevel::Madya, $material->level);
        $this->assertNotNull($material->file_path);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'model_type' => Material::class,
        ]);
    }

    public function test_materi_wajib_file_pdf(): void
    {
        Livewire::actingAs($this->pengurus())
            ->test(MaterialIndex::class)
            ->set([
                'title' => 'Tanpa File',
                'category' => 'lainnya',
                'level' => 'mula',
                'order' => 1,
                'file' => null,
            ])
            ->call('save')
            ->assertHasErrors(['file']);
    }

    public function test_anggota_bisa_buka_daftar_materi(): void
    {
        Material::factory()->create(['title' => 'Materi Publik Panel']);

        Livewire::actingAs($this->anggota())
            ->test(MaterialBrowse::class)
            ->assertSee('Materi Publik Panel');
    }

    public function test_pengurus_dapat_buat_quiz_dengan_soal(): void
    {
        $pengurus = $this->pengurus();

        Livewire::actingAs($pengurus)
            ->test(QuizCreate::class)
            ->set([
                'title' => 'Quiz Baru',
                'description' => 'Deskripsi',
                'max_attempts' => 2,
                'pass_score' => 75,
                'is_active' => true,
                'time_limit_minutes' => 20,
                'questions' => [
                    [
                        'question' => '1+1=?',
                        'score' => 10,
                        'options' => [
                            ['text' => '2', 'is_correct' => true],
                            ['text' => '3', 'is_correct' => false],
                        ],
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('quizzes.index'));

        $quiz = Quiz::where('title', 'Quiz Baru')->firstOrFail();
        $this->assertSame($pengurus->id, $quiz->created_by);
        $this->assertSame(1, $quiz->questions()->count());
        $this->assertSame(2, $quiz->questions()->first()->options()->count());
    }

    public function test_anggota_ditolak_buat_quiz(): void
    {
        $this->actingAs($this->anggota())->get(route('quizzes.create'))->assertForbidden();
    }

    public function test_anggota_dapat_lihat_quiz_aktif_saja(): void
    {
        $this->makeQuiz(['title' => 'Quiz Aktif', 'is_active' => true]);
        $this->makeQuiz(['title' => 'Quiz Nonaktif', 'is_active' => false]);

        Livewire::actingAs($this->anggota())
            ->test(QuizIndex::class)
            ->assertSee('Quiz Aktif')
            ->assertDontSee('Quiz Nonaktif');
    }

    public function test_pengurus_dapat_lihat_semua_quiz(): void
    {
        $this->makeQuiz(['title' => 'Quiz Nonaktif', 'is_active' => false]);

        Livewire::actingAs($this->pengurus())
            ->test(QuizIndex::class)
            ->assertSee('Quiz Nonaktif');
    }

    public function test_attempt_menghasilkan_skor_benar_dan_sertifikat(): void
    {
        Storage::fake('public');
        [$user, $member] = $this->anggotaWithMember();
        $quiz = $this->makeQuiz(['pass_score' => 70, 'max_attempts' => 3]);

        $question = $quiz->questions->first();
        $correct = $question->options->firstWhere('is_correct', true);

        $attempt = (new StartQuizAttempt)->execute($quiz, $member->id, '127.0.0.1');
        $this->assertSame(QuizAttemptStatus::Running, $attempt->status);

        $attempt = (new SubmitQuizAttempt)->execute($attempt, [
            $question->id => $correct->id,
        ]);

        $this->assertSame(QuizAttemptStatus::Finished, $attempt->status);
        $this->assertSame(100, $attempt->score);

        $this->assertDatabaseHas('certificates', [
            'member_id' => $member->id,
            'source_type' => 'quiz',
            'source_id' => $quiz->id,
            'is_revoked' => false,
        ]);
    }

    public function test_skor_sebagian_dihitung_dari_persen(): void
    {
        Storage::fake('public');
        [$user, $member] = $this->anggotaWithMember();
        $quiz = $this->makeQuiz(['max_attempts' => 3]);

        // Tambah soal kedua agar bisa 50%
        $q2 = QuizQuestion::factory()->create([
            'quiz_id' => $quiz->id,
            'question' => 'Soal 2?',
            'score' => 10,
            'order' => 1,
        ]);
        QuizOption::factory()->correct()->create(['question_id' => $q2->id, 'option_text' => 'Ya', 'order' => 0]);
        QuizOption::factory()->create(['question_id' => $q2->id, 'option_text' => 'Tidak', 'order' => 1]);

        $quiz = $quiz->fresh(['questions.options']);
        $q1 = $quiz->questions->firstWhere('question', 'Soal 1?');
        $correct1 = $q1->options->firstWhere('is_correct', true);
        $wrong2 = $quiz->questions->firstWhere('question', 'Soal 2?')
            ->options->firstWhere('is_correct', false);

        $attempt = (new StartQuizAttempt)->execute($quiz, $member->id);
        $attempt = (new SubmitQuizAttempt)->execute($attempt, [
            $q1->id => $correct1->id,
            $q2->id => $wrong2->id,
        ]);

        $this->assertSame(50, $attempt->score);
        $this->assertDatabaseMissing('certificates', [
            'member_id' => $member->id,
            'source_id' => $quiz->id,
            'is_revoked' => false,
        ]);
    }

    public function test_batas_attempt_dipatuhi(): void
    {
        [$user, $member] = $this->anggotaWithMember();
        $quiz = $this->makeQuiz(['max_attempts' => 1]);

        QuizAttempt::factory()->finished()->create([
            'quiz_id' => $quiz->id,
            'member_id' => $member->id,
            'attempt_no' => 1,
        ]);

        $this->expectException(ValidationException::class);
        (new StartQuizAttempt)->execute($quiz, $member->id);
    }

    public function test_waktu_habis_menjadikan_expired_dan_submit_ditolak(): void
    {
        [$user, $member] = $this->anggotaWithMember();
        $quiz = $this->makeQuiz(['time_limit_minutes' => 1, 'max_attempts' => 3]);

        $attempt = QuizAttempt::factory()->create([
            'quiz_id' => $quiz->id,
            'member_id' => $member->id,
            'started_at' => now()->subMinutes(5),
            'status' => QuizAttemptStatus::Running,
        ]);

        $this->assertTrue($attempt->isExpired());

        $question = $quiz->questions->first();

        try {
            (new SubmitQuizAttempt)->execute($attempt, [$question->id => $question->options->first()->id]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Waktu quiz sudah habis', $e->errors()['quiz'][0] ?? '');
        }

        $this->assertSame(QuizAttemptStatus::Expired, $attempt->fresh()->status);
    }

    public function test_grade_hanya_untuk_attempt_finished(): void
    {
        $attempt = QuizAttempt::factory()->create(['status' => QuizAttemptStatus::Running]);

        $this->expectException(ValidationException::class);
        (new GradeQuizAttempt)->execute($attempt);
    }

    public function test_quiz_take_membuat_attempt_dan_submit(): void
    {
        Storage::fake('public');
        [$user, $member] = $this->anggotaWithMember();
        $quiz = $this->makeQuiz(['max_attempts' => 3]);
        $question = $quiz->questions->first();
        $correct = $question->options->firstWhere('is_correct', true);

        $component = Livewire::actingAs($user)
            ->test(QuizTake::class, ['quiz' => $quiz]);

        $component
            ->set(['answers' => [$question->id => $correct->id]])
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('results.index'));

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'member_id' => $member->id,
            'status' => 'finished',
            'score' => 100,
        ]);
    }

    public function test_quiz_take_tanpa_jawaban_ditolak(): void
    {
        [$user, $member] = $this->anggotaWithMember();
        $quiz = $this->makeQuiz();

        Livewire::actingAs($user)
            ->test(QuizTake::class, ['quiz' => $quiz])
            ->set(['answers' => []])
            ->call('submit')
            ->assertHasErrors();
    }

    public function test_quiz_nonaktif_tidak_bisa_dikerjakan(): void
    {
        [$user, $member] = $this->anggotaWithMember();
        $quiz = $this->makeQuiz(['is_active' => false]);

        $this->actingAs($user)->get(route('quizzes.take', $quiz))->assertForbidden();
    }

    public function test_tanpa_profil_anggota_ditolak_quiz_take(): void
    {
        $user = $this->anggota();
        $quiz = $this->makeQuiz();

        $this->actingAs($user)
            ->get(route('quizzes.take', $quiz))
            ->assertForbidden()
            ->assertSee('belum terhubung ke profil anggota');
    }

    public function test_resume_running_attempt_saat_mount(): void
    {
        [$user, $member] = $this->anggotaWithMember();
        $quiz = $this->makeQuiz(['max_attempts' => 3, 'time_limit_minutes' => 30]);

        $running = QuizAttempt::factory()->create([
            'quiz_id' => $quiz->id,
            'member_id' => $member->id,
            'attempt_no' => 1,
            'started_at' => now()->subMinutes(5),
            'status' => QuizAttemptStatus::Running,
        ]);

        Livewire::actingAs($user)
            ->test(QuizTake::class, ['quiz' => $quiz])
            ->assertSet('attemptId', $running->id);

        $this->assertSame(1, $quiz->attempts()->count());
    }

    public function test_action_expire_menandai_attempt_lewat_waktu(): void
    {
        $quiz = $this->makeQuiz(['time_limit_minutes' => 30]);

        $stale = QuizAttempt::factory()->create([
            'quiz_id' => $quiz->id,
            'started_at' => now()->subMinutes(45),
            'status' => QuizAttemptStatus::Running,
        ]);
        $fresh = QuizAttempt::factory()->create([
            'quiz_id' => $quiz->id,
            'attempt_no' => 2,
            'started_at' => now()->subMinutes(5),
            'status' => QuizAttemptStatus::Running,
        ]);
        $finished = QuizAttempt::factory()->finished(80)->create([
            'quiz_id' => $quiz->id,
            'attempt_no' => 3,
        ]);

        $count = (new ExpireStaleQuizAttempts)->execute();

        $this->assertSame(1, $count);
        $this->assertSame(QuizAttemptStatus::Expired, $stale->fresh()->status);
        $this->assertSame(QuizAttemptStatus::Running, $fresh->fresh()->status);
        $this->assertSame(QuizAttemptStatus::Finished, $finished->fresh()->status);
    }
}
