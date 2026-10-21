<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MaterialDownloadController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Material $material): Response
    {
        $this->authorize('view', $material);

        if (! $material->file_path || ! Storage::disk('local')->exists($material->file_path)) {
            abort(404, 'File materi belum tersedia.');
        }

        return Storage::disk('local')->response(
            $material->file_path,
            $material->title.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
