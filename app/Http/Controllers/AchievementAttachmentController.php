<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AchievementAttachmentController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Achievement $achievement): Response
    {
        $this->authorize('view', $achievement);

        if (! $achievement->attachment_path || ! Storage::disk('local')->exists($achievement->attachment_path)) {
            abort(404, 'Lampiran tidak ditemukan.');
        }

        return Storage::disk('local')->response(
            $achievement->attachment_path,
        );
    }
}
