<?php

namespace App\Actions\Articles;

use App\Models\Article;
use App\Services\AuditLogger;
use App\Services\ContentSanitizer;

class UpdateArticle
{
    public function execute(Article $article, array $data, ?\Illuminate\Http\UploadedFile $cover = null): Article
    {
        if (array_key_exists('content', $data)) {
            $data['content'] = ContentSanitizer::sanitize($data['content']);
        }

        if ($cover) {
            if ($article->cover_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($article->cover_path);
            }
            $data['cover_path'] = $cover->store('articles/'.$article->id, 'public');
        }

        $old = $article->getAttributes();
        $article->update($data);

        app(AuditLogger::class)->log(
            'updated',
            $article,
            $old,
            $article->getChanges(),
        );

        return $article;
    }
}
