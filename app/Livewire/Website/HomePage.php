<?php

namespace App\Livewire\Website;

use App\Models\Achievement;
use App\Models\Article;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Member;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Beranda'])]
class HomePage extends Component
{
    public function render()
    {
        $stats = [
            'members' => Member::query()
                ->where('status', 'aktif')
                ->count(),
            'events' => Event::query()
                ->where('is_public', true)
                ->whereIn('status', ['published', 'selesai'])
                ->count(),
            'achievements' => Achievement::query()
                ->where('is_public', true)
                ->count(),
            'articles' => Article::query()
                ->where('is_public', true)
                ->where('status', 'published')
                ->count(),
        ];

        $upcomingEvents = Event::query()
            ->where('is_public', true)
            ->where('status', 'published')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'type', 'location', 'starts_at']);

        $latestArticles = Article::query()
            ->where('is_public', true)
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'excerpt', 'cover_path', 'published_at', 'category']);

        $galleryPreview = GalleryItem::query()
            ->where('is_public', true)
            ->latest()
            ->limit(6)
            ->get(['id', 'title', 'file_path', 'file_type', 'video_url']);

        $featuredAchievements = Achievement::query()
            ->where('is_public', true)
            ->orderByDesc('year')
            ->limit(3)
            ->get(['id', 'title', 'level', 'year', 'winner']);

        return view('livewire.website.home', [
            'stats' => $stats,
            'upcomingEvents' => $upcomingEvents,
            'latestArticles' => $latestArticles,
            'galleryPreview' => $galleryPreview,
            'featuredAchievements' => $featuredAchievements,
            'orgName' => Setting::get('org_name', config('app.name')),
            'vision' => Setting::get('org_vision', ''),
            'mission' => Setting::get('org_mission', ''),
        ]);
    }
}
