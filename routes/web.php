<?php

use App\Http\Controllers\AttendanceReportPdfController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Livewire\Achievements\AchievementIndex;
use App\Livewire\AdminArea;
use App\Livewire\Articles\ArticleCreate;
use App\Livewire\Articles\ArticleEdit;
use App\Livewire\Articles\ArticleIndex;
use App\Livewire\Attendance\AttendanceRecap;
use App\Livewire\AuditLogs\AuditLogIndex;
use App\Livewire\Certificates\CertificateIndex;
use App\Livewire\Certificates\MyCertificates;
use App\Livewire\Dashboard;
use App\Livewire\Events\EventCreate;
use App\Livewire\Events\EventEdit;
use App\Livewire\Events\EventIndex;
use App\Livewire\Events\EventShow;
use App\Livewire\Gallery\GalleryIndex;
use App\Livewire\Masters\BatchIndex;
use App\Livewire\Masters\ClassIndex;
use App\Livewire\Masters\PositionIndex;
use App\Livewire\Materials\MaterialBrowse;
use App\Livewire\Materials\MaterialIndex;
use App\Livewire\Members\MemberCreate;
use App\Livewire\Members\MemberEdit;
use App\Livewire\Members\MemberExport;
use App\Livewire\Members\MemberImport;
use App\Livewire\Members\MemberIndex;
use App\Livewire\Members\MemberShow;
use App\Livewire\Messages\MessageIndex;
use App\Livewire\Notifications\NotificationsIndex;
use App\Livewire\PengurusArea;
use App\Livewire\Programs\ProgramIndex;
use App\Livewire\Quizzes\QuizCreate;
use App\Livewire\Quizzes\QuizEdit;
use App\Livewire\Quizzes\QuizIndex;
use App\Livewire\Quizzes\QuizTake;
use App\Livewire\Quizzes\ResultShow;
use App\Livewire\Reports\AttendanceRecap as AttendanceReport;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Settings\OrganizationProfile as AdminOrganizationProfile;
use App\Livewire\Settings\SettingIndex;
use App\Livewire\Stats\EventStats;
use App\Livewire\Stats\MemberStats;
use App\Livewire\Users\UserCreate;
use App\Livewire\Users\UserEdit;
use App\Livewire\Users\UserIndex;
use App\Livewire\Website\AchievementDirectory;
use App\Livewire\Website\ArticleDirectory;
use App\Livewire\Website\ArticleShow;
use App\Livewire\Website\CertificateVerify;
use App\Livewire\Website\ContactPage;
use App\Livewire\Website\EventDirectory;
use App\Livewire\Website\EventProfile;
use App\Livewire\Website\GalleryDirectory;
use App\Livewire\Website\HomePage;
use App\Livewire\Website\MemberDirectory;
use App\Livewire\Website\MemberProfile;
use App\Livewire\Website\OrganizationProfile as PublicOrganizationProfile;
use Illuminate\Support\Facades\Route;

// Halaman publik: throttle global 60/menit/IP (docs/08 §2.6, limiter 'public' di AppServiceProvider).
Route::middleware('throttle:public')->group(function () {
    Route::get('/', HomePage::class)->name('home');

    Route::get('/profil', PublicOrganizationProfile::class)->name('public.profile');
    Route::get('/berita', ArticleDirectory::class)->name('public.articles.index');
    Route::get('/berita/{slug}', ArticleShow::class)->name('public.articles.show');
    Route::get('/galeri', GalleryDirectory::class)->name('public.gallery.index');
    Route::get('/galeri/{categorySlug}', GalleryDirectory::class)->name('public.gallery.category');
    Route::get('/prestasi', AchievementDirectory::class)->name('public.achievements.index');
    Route::get('/kontak', ContactPage::class)->name('public.contact');

    Route::get('/anggota', MemberDirectory::class)->name('public.members.index');
    Route::get('/anggota/{slug}', MemberProfile::class)->name('public.members.show');
    Route::get('/kegiatan', EventDirectory::class)->name('public.events.index');
    Route::get('/kegiatan/{slug}', EventProfile::class)->name('public.events.show');
    Route::get('/verifikasi/{code?}', CertificateVerify::class)->name('verify.certificate')->middleware('throttle:30,1');
    Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
    Route::get('/robots.txt', RobotsController::class)->name('robots');
});

// Check-in punya limiter sendiri (CheckInController); /health untuk uptime monitor — di luar throttle publik.
Route::get('/check-in/{token}', [CheckInController::class, 'show'])->name('check-in');
Route::post('/check-in/{token}', [CheckInController::class, 'store'])->name('check-in.store');
Route::get('/health', HealthController::class)->name('health');

Route::middleware('auth')->group(function () {
    Route::get('/panel', Dashboard::class)->name('panel.home');
    Route::get('/panel/profil', \App\Livewire\Profile\ProfileIndex::class)->name('panel.profile');
    Route::put('/panel/profil', function (\Illuminate\Http\Request $request, \App\Actions\Profile\UpdateProfile $updater) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore(auth()->id())],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email_pribadi' => ['nullable', 'email', 'max:255'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $updater->execute(auth()->user(), $validated);

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    })->name('panel.profile.update');

    Route::middleware('role:admin|pengurus')->group(function () {
        Route::get('/panel/pengurus', PengurusArea::class)->name('panel.pengurus');

        Route::get('/panel/members', MemberIndex::class)->name('members.index');
        Route::get('/panel/members/create', MemberCreate::class)->name('members.create');
        Route::get('/panel/members/import', MemberImport::class)->name('members.import');
        Route::get('/panel/members/export', MemberExport::class)->name('members.export');
        Route::get('/panel/members/{member}', MemberShow::class)->name('members.show');
        Route::get('/panel/members/{member}/edit', MemberEdit::class)->name('members.edit');

        Route::get('/panel/events', EventIndex::class)->name('events.index');
        Route::get('/panel/events/create', EventCreate::class)->name('events.create');
        Route::get('/panel/events/{event}', EventShow::class)->name('events.show');
        Route::get('/panel/events/{event}/edit', EventEdit::class)->name('events.edit');

        Route::get('/panel/programs', ProgramIndex::class)->name('programs.index');
        Route::get('/panel/attendance', AttendanceRecap::class)->name('attendance.recap');

        Route::get('/panel/laporan', ReportIndex::class)->name('reports.index');
        Route::get('/panel/laporan/absensi', AttendanceReport::class)->name('reports.attendance');
        Route::get('/panel/laporan/absensi/pdf', AttendanceReportPdfController::class)->name('reports.attendance.pdf');
        Route::get('/panel/statistik/anggota', MemberStats::class)->name('stats.members');
        Route::get('/panel/statistik/kegiatan', EventStats::class)->name('stats.events');

        Route::get('/panel/articles', ArticleIndex::class)->name('articles.index');
        Route::get('/panel/articles/create', ArticleCreate::class)->name('articles.create');
        Route::get('/panel/articles/{article}/edit', ArticleEdit::class)->name('articles.edit');

        Route::get('/panel/gallery', GalleryIndex::class)->name('gallery.index');
        Route::get('/panel/achievements', AchievementIndex::class)->name('achievements.index');
        Route::get('/panel/achievements/{achievement}/download', \App\Http\Controllers\AchievementAttachmentController::class)->name('achievements.download');
        Route::get('/panel/messages', MessageIndex::class)->name('messages.index');

        Route::get('/panel/materi/manage', MaterialIndex::class)->name('materials.manage');
        Route::get('/panel/quiz/create', QuizCreate::class)->name('quizzes.create');
        Route::get('/panel/quiz/{quiz}/edit', QuizEdit::class)->name('quizzes.edit');
        Route::get('/panel/sertifikat/manage', CertificateIndex::class)->name('certificates.manage');
    });

    // Notifikasi — semua role login
    Route::get('/panel/notifikasi', NotificationsIndex::class)->name('notifications.index');

    // Academy — semua role login (anggota+)
    Route::get('/panel/materi', MaterialBrowse::class)->name('materials.index');
    Route::get('/panel/materi/{material}/download', \App\Http\Controllers\MaterialDownloadController::class)->name('materials.download');
    Route::get('/panel/quiz', QuizIndex::class)->name('quizzes.index');
    Route::get('/panel/quiz/{quiz}', QuizTake::class)->name('quizzes.take');
    Route::get('/panel/nilai', ResultShow::class)->name('results.index');
    Route::get('/panel/sertifikat', MyCertificates::class)->name('certificates.mine');

    Route::middleware('role:admin')->group(function () {
        Route::get('/panel/admin', AdminArea::class)->name('panel.admin');

        Route::get('/panel/admin/users', UserIndex::class)->name('admin.users.index');
        Route::get('/panel/admin/users/create', UserCreate::class)->name('admin.users.create');
        Route::get('/panel/admin/users/{user}/edit', UserEdit::class)->name('admin.users.edit');

        Route::get('/panel/admin/settings', SettingIndex::class)->name('admin.settings.index');
        Route::get('/panel/admin/settings/organization', AdminOrganizationProfile::class)->name('admin.settings.organization');

        Route::get('/panel/admin/audit-log', AuditLogIndex::class)->name('admin.audit-log.index');

        Route::get('/panel/admin/master/batches', BatchIndex::class)->name('admin.master.batches');
        Route::get('/panel/admin/master/classes', ClassIndex::class)->name('admin.master.classes');
        Route::get('/panel/admin/master/positions', PositionIndex::class)->name('admin.master.positions');
    });
});

require __DIR__.'/auth.php';
