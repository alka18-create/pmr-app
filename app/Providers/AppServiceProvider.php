<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Achievement;
use App\Models\Article;
use App\Models\AttendanceRecord;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\User;
use App\Policies\AchievementPolicy;
use App\Policies\ArticlePolicy;
use App\Policies\AttendancePolicy;
use App\Policies\CertificatePolicy;
use App\Policies\EventPolicy;
use App\Policies\GalleryItemPolicy;
use App\Policies\MaterialPolicy;
use App\Policies\QuizPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(AttendanceRecord::class, AttendancePolicy::class);
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(GalleryItem::class, GalleryItemPolicy::class);
        Gate::policy(Achievement::class, AchievementPolicy::class);
        Gate::policy(Material::class, MaterialPolicy::class);
        Gate::policy(Quiz::class, QuizPolicy::class);
        Gate::policy(Certificate::class, CertificatePolicy::class);

        Gate::before(function (User $user, string $ability) {
            return $user->role === UserRole::Admin ? true : null;
        });

        Gate::define('access-pengurus-area', fn (User $user) => $user->role === UserRole::Pengurus);
        Gate::define('access-admin-area', fn (User $user) => $user->role === UserRole::Admin);
        Gate::define('manage-master', fn (User $user) => $user->role === UserRole::Admin);
    }
}
